<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use SOFe\AwaitGenerator\Await;

class SendGameRule extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $player = "", private string $gamerule = "", private bool $value = true) {
        parent::__construct(self::SEND_BOOL_GAMERULE, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "gamerule", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getGamerule(), $this->getValue() ? "true" : "false"];
    }

    public function setGamerule(string $gamerule): void {
        $this->gamerule = $gamerule;
    }

    public function getGamerule(): string {
        return $this->gamerule;
    }

    public function setValue(bool $value): void {
        $this->value = $value;
    }

    public function getValue(): bool {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->gamerule !== "";
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $gamerule = $source->replaceVariables($this->getGamerule());
        $player = $this->getOnlinePlayer($source);

        $pk = GameRulesChangedPacket::create([
            $gamerule => new BoolGameRule($this->getValue(), true),
        ]);
        $player->getNetworkSession()->sendDataPacket($pk);
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.setGamerule.form.gamerule", "showcoordinates", $this->getGamerule(), true),
            new Toggle("@action.setGamerule.form.value", $this->getValue()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->setGamerule($content[1]);
        $this->setValue($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getGamerule(), $this->getValue()];
    }
}