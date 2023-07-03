<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\IsLocalVariableArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\MapVariable;
use SOFe\AwaitGenerator\Await;

class AddMapVariable extends SimpleAction {

    public function __construct(string $variableName = "", string $variableKey = "", string $variableValue = "", bool $isLocal = true) {
        parent::__construct(self::ADD_MAP_VARIABLE, FlowItemCategory::VARIABLE);

        $this->setArguments([
            new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo"),
            new StringArgument("key", $variableKey, "@action.variable.form.key", example: "auieo"),
            new StringArgument("value", $variableValue, "@action.variable.form.value", example: "aeiuo", optional: true),
            new IsLocalVariableArgument("scope", $isLocal),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getVariableKey(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getVariableValue(): StringArgument {
        return $this->getArguments()[2];
    }

    public function getIsLocal(): BooleanArgument {
        return $this->getArguments()[3];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();
        $name = $this->getVariableName()->getString($source);
        $key = $this->getVariableKey()->getString($source);

        $value = $this->getVariableValue()->get();
        $addVariable = $helper->copyOrCreateVariable($value, $source);
        $variable = $this->getIsLocal()->getBool() ? $source->getVariable($name) : $helper->get($name);
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof MapVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }
        $variable->setValueAt($key, $addVariable);

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->getVariableName()->get() => new DummyVariable(MapVariable::class)
        ];
    }
}
