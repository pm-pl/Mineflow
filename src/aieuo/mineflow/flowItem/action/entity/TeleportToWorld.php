<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class TeleportToWorld extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;
    private StringArgument $worldName;

    public function __construct(string $entity = "", string $worldName = "", private bool $safeSpawn = true) {
        parent::__construct(self::TELEPORT_TO_WORLD, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
        $this->worldName = new StringArgument("world", $worldName, "@action.createPosition.form.world", example: "world");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->worldName->get()];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getWorldName(): StringArgument {
        return $this->worldName;
    }

    public function isSafeSpawn(): bool {
        return $this->safeSpawn;
    }

    public function setSafeSpawn(bool $safeSpawn): void {
        $this->safeSpawn = $safeSpawn;
    }

    public function isDataValid(): bool {
        return $this->entity->isValid() and $this->worldName->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $this->worldName->getString($source);

        $worldManager = Server::getInstance()->getWorldManager();
        $worldManager->loadWorld($worldName);
        $world = $worldManager->getWorldByName($worldName);
        if ($world === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $entity = $this->entity->getOnlineEntity($source);

        $pos = $this->safeSpawn ? $world->getSafeSpawn() : $world->getSpawnLocation();
        $entity->teleport($pos);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
           $this->worldName->createFormElement($variables),
            new Toggle("@action.teleportToWorld.form.safespawn", $this->isSafeSpawn()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->worldName->set($content[1]);
        $this->setSafeSpawn($content[2]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->worldName->get(), $this->isSafeSpawn()];
    }
}
