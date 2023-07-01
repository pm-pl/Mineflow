<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\exception\FlowItemExecutionException;
use aieuo\mineflow\exception\MineflowMethodErrorException;
use aieuo\mineflow\exception\RecipeInterruptException;
use aieuo\mineflow\exception\UndefinedMineflowMethodException;
use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UndefinedMineflowVariableException;
use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\Event;
use SOFe\AwaitGenerator\Await;
use function count;

class FlowItemExecutor {

    private mixed $lastResult;

    private FlowItem $currentFlowItem;
    private int $currentIndex;

    /**
     * @param FlowItem[] $items
     * @param Entity|null $target
     * @param Variable[] $variables
     * @param FlowItemExecutor|null $parent
     * @param Event|null $event
     * @param \Closure|null $onComplete
     * @param \Closure|null $onError
     * @param Recipe|null $sourceRecipe
     */
    public function __construct(
        private array     $items,
        private ?Entity   $target,
        private array     $variables = [],
        private ?self     $parent = null,
        private ?Event    $event = null,
        private ?\Closure $onComplete = null,
        private ?\Closure $onError = null,
        private ?Recipe   $sourceRecipe = null
    ) {
        if ($event === null and $parent !== null) {
            $this->event = $parent->getEvent();
        }
    }

    public function getGenerator(): \Generator {
        $maxIndex = count($this->items) - 1;
        $this->currentIndex = 0;

        while ($this->currentIndex <= $maxIndex) {
            $this->currentFlowItem = $this->items[$this->currentIndex];
            $this->lastResult = yield from $this->currentFlowItem->execute($this);
            $this->currentIndex++;
        }
    }

    public function restart(): void {
        if ($this->parent === null) {
            $this->currentIndex = -1;
        } else {
            $this->currentIndex = count($this->items);
            $this->parent->restart();
        }
    }

    public function execute(): bool {
        Await::f2c(function () {
            try {
                yield from $this->getGenerator();
            } catch (InvalidFlowValueException $e) {
                Logger::warning(Language::get("action.error", [$e->getFlowItemName(), $e->getMessage()]), $this->target);
                if ($this->onError !== null) ($this->onError)($this->currentIndex, $this->currentFlowItem, $this->target);
            } catch (UndefinedMineflowVariableException|UndefinedMineflowPropertyException|UndefinedMineflowMethodException|MineflowMethodErrorException|UnsupportedCalculationException $e) {
                if (!empty($e->getMessage())) Logger::warning($e->getMessage(), $this->target);
                if ($this->onError !== null) ($this->onError)($this->currentIndex, $this->currentFlowItem, $this->target);
            } catch (RecipeInterruptException) {
                // ignored
            }

            if ($this->onComplete !== null) ($this->onComplete)($this);
        });
        return true;
    }

    public function getTarget(): ?Entity {
        return $this->target;
    }

    public function getLastResult() {
        return $this->lastResult;
    }

    public function getEvent(): ?Event {
        return $this->event;
    }

    public function getSourceRecipe(): ?Recipe {
        if ($this->parent !== null) return $this->parent->sourceRecipe;

        return $this->sourceRecipe;
    }

    public function replaceVariables(string $text): string {
        return Mineflow::getVariableHelper()->replaceVariables($text, $this->getVariables());
    }

    public function getVariable(string $name): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);

        $variable = $this->variables[$name] ?? ($this->parent?->getVariable($name));

        if ($variable === null) return null;

        foreach ($names as $name1) {
            if (!($variable instanceof ListVariable) and !($variable instanceof ObjectVariable)) return null;
            $variable = $variable->getProperty($name1);
        }
        return $variable;
    }

    public function getVariables(): array {
        $variables = $this->variables;
        if ($this->parent !== null) {
            $variables = array_merge($this->parent->getVariables(), $variables);
        }
        return $variables;
    }

    public function addVariable(string $name, Variable $variable, bool $onlyThisScope = false): void {
        $this->variables[$name] = $variable;

        if (!$onlyThisScope and $this->parent !== null) {
            $this->parent->addVariable($name, $variable);
        }
    }

    public function removeVariable(string $name): void {
        unset($this->variables[$name]);
        $this->parent?->removeVariable($name);
    }

    public function getRootExecutor(): FlowItemExecutor {
        return $this->parent?->getRootExecutor() ?? $this;
    }
}
