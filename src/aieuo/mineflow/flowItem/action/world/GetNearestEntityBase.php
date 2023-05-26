<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PositionVariableDropdown;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use pocketmine\entity\Entity;
use SOFe\AwaitGenerator\Await;

abstract class GetNearestEntityBase extends FlowItem implements PositionFlowItem {
    use PositionFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $id,
        string         $category = FlowItemCategory::WORLD,
        string         $position = "",
        private string $maxDistance = "100",
        private string $resultName = "entity"
    ) {
        parent::__construct($id, $category);

        $this->setPositionVariableName($position);
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "distance", "entity"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPositionVariableName(), $this->getResultName()];
    }

    /**
     * @return class-string<Entity>
     */
    abstract public function getTargetClass(): string;

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function setMaxDistance(string $maxDistance): void {
        $this->maxDistance = $maxDistance;
    }

    public function getMaxDistance(): string {
        return $this->maxDistance;
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $position = $this->getPosition($source);
        $result = $source->replaceVariables($this->getResultName());
        $maxDistance = $this->getFloat($source->replaceVariables($this->getMaxDistance()));

        $entity = $position->world->getNearestEntity($position, $maxDistance, $this->getTargetClass());

        $variable = $entity === null ? new NullVariable() : EntityVariable::fromObject($entity);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PositionVariableDropdown($variables, $this->getPositionVariableName()),
            new ExampleNumberInput("@action.getNearestEntity.form.maxDistance", "100", $this->getMaxDistance(), true),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPositionVariableName($content[0]);
        $this->setMaxDistance($content[1]);
        $this->setResultName($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName(), $this->getMaxDistance(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(EntityVariable::class, "nullable")
        ];
    }
}