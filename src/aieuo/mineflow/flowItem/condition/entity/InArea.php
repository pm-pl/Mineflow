<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class InArea extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PositionArgument $position1;
    private PositionArgument $position2;
    private EntityArgument $entity;

    public function __construct(string $entity = "", string $pos1 = "", string $pos2 = "") {
        parent::__construct(self::IN_AREA, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("target", $entity);
        $this->position1 = new PositionArgument("pos1", $pos1, "@condition.inArea.form.pos1");
        $this->position2 = new PositionArgument("pos2", $pos2, "@condition.inArea.form.pos2");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "pos1", "pos2"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->position1->get(), $this->position2->get()];
    }

    public function isDataValid(): bool {
        return $this->entity->isValid() and $this->position1->isValid() and $this->position2->isValid();
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getPosition1(): PositionArgument {
        return $this->position1;
    }

    public function getPosition2(): PositionArgument {
        return $this->position2;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $pos1 = $this->position1->getPosition($source);
        $pos2 = $this->position2->getPosition($source);
        $pos = $entity->getLocation()->floor();

        yield Await::ALL;
        return $pos->x >= min($pos1->x, $pos2->x) and $pos->x <= max($pos1->x, $pos2->x)
            and $pos->y >= min($pos1->y, $pos2->y) and $pos->y <= max($pos1->y, $pos2->y)
            and $pos->z >= min($pos1->z, $pos2->z) and $pos->z <= max($pos1->z, $pos2->z);
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            $this->position1->createFormElement($variables),
            $this->position2->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->position1->set($content[1]);
        $this->position2->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->position1->get(), $this->position2->get()];
    }
}
