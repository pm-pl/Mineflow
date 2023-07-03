<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class TransferServer extends SimpleAction {

    public function __construct(string $player = "", string $ip = "", int $port = 19132) {
        parent::__construct(self::TRANSFER_SERVER, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new StringArgument("ip", $ip, example: "aieuo.tokyo"),
            new NumberArgument("port", $port, example: "19132", min: 1, max: 65535),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getIp(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getPort(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $ip = $this->getIp()->getString($source);
        $port = $this->getPort()->getInt($source);

        $player = $this->getPlayer()->getOnlinePlayer($source);
        $player->transfer($ip, $port);
        yield Await::ALL;
    }
}
