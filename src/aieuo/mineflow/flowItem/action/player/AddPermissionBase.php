<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;

abstract class AddPermissionBase extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = "",
        private string $playerPermission = ""
    ) {
        parent::__construct($id, $category);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "permission"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getPlayerPermission()];
    }

    public function getPermissions(): array {
        return [self::PERMISSION_PERMISSION];
    }

    public function setPlayerPermission(string $playerPermission): void {
        $this->playerPermission = $playerPermission;
    }

    public function getPlayerPermission(): string {
        return $this->playerPermission;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->playerPermission !== "";
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@condition.hasPermission.form.permission", "mineflow.customcommand.op", $this->getPlayerPermission(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setPlayerPermission($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPlayerPermission()];
    }
}