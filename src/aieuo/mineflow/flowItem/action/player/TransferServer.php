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
use SOFe\AwaitGenerator\Await;

class TransferServer extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $player = "", private string $ip = "", private string $port = "19132") {
        parent::__construct(self::TRANSFER_SERVER, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "ip", "port"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getIp(), $this->getPort()];
    }

    public function setIp(string $ip): void {
        $this->ip = $ip;
    }

    public function getIp(): string {
        return $this->ip;
    }

    public function setPort(string $port): void {
        $this->port = $port;
    }

    public function getPort(): string {
        return $this->port;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->ip !== "" and $this->port !== "";
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $ip = $source->replaceVariables($this->getIp());
        $port = $this->getInt($source->replaceVariables($this->getPort()), 1, 65535);

        $player = $this->getOnlinePlayer($source);
        $player->transfer($ip, $port);
        Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.transfer.form.ip", "aieuo.tokyo", $this->getIp()),
            new ExampleInput("@action.transfer.form.port", "19132", $this->getIp()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerVariableName($content[0]);
        $this->setIp($content[1]);
        $this->setPort($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getIp(), $this->getPort()];
    }
}
