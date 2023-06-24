<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class AddEffect extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private bool $visible = false;
    private EntityPlaceholder $entity;

    public function __construct(
        string         $entity = "",
        private string $effectId = "",
        private string $time = "300",
        private string $power = "1"
    ) {
        parent::__construct(self::ADD_EFFECT, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "id", "power", "time"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getEffectId(), $this->getPower(), $this->getTime()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function setEffectId(string $effectId): void {
        $this->effectId = $effectId;
    }

    public function getEffectId(): string {
        return $this->effectId;
    }

    public function setPower(string $power): void {
        $this->power = $power;
    }

    public function getPower(): string {
        return $this->power;
    }

    public function setTime(string $time): void {
        $this->time = $time;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->effectId !== "" and $this->power !== "" and $this->time !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $effectId = $source->replaceVariables($this->getEffectId());
        $time = $this->getInt($source->replaceVariables($this->getTime()));
        $power = $this->getInt($source->replaceVariables($this->getPower()));
        $entity = $this->entity->getOnlineEntity($source);

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        if ($entity instanceof Living) {
            $entity->getEffects()->add(new EffectInstance($effect, $time * 20, $power - 1, $this->visible));
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleInput("@action.addEffect.form.effect", "1", $this->getEffectId(), true),
            new ExampleNumberInput("@action.addEffect.form.time", "300", $this->getTime(), true, 1),
            new ExampleNumberInput("@action.addEffect.form.power", "1", $this->getPower(), true),
            new Toggle("@action.addEffect.form.visible", $this->visible),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setEffectId($content[1]);
        $this->setTime($content[2]);
        $this->setPower($content[3]);
        $this->visible = $content[4];
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getEffectId(), $this->getTime(), $this->getPower(), $this->visible];
    }
}
