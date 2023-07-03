<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Bossbar;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use SOFe\AwaitGenerator\Await;
use function array_keys;

class ShowBossbar extends SimpleAction {

    private array $colors = [
        "pink" => BossBarColor::PINK,
        "blue" => BossBarColor::BLUE,
        "red" => BossBarColor::RED,
        "green" => BossBarColor::GREEN,
        "yellow" => BossBarColor::YELLOW,
        "purple" => BossBarColor::PURPLE,
        "white" => BossBarColor::WHITE,
    ];

    public function __construct(
        string $player = "",
        string $title = "",
        float  $max = 0,
        float  $value = 0,
        string $color = "purple",
        string $barId = ""
    ) {
        parent::__construct(self::SHOW_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new StringArgument("title", $title, example: "title"),
            new NumberArgument("max", $max, example: "20", min: 1),
            new NumberArgument("value", $value, example: "20"),
            new StringEnumArgument("color", $color, array_keys($this->colors)),
            new StringArgument("id", $barId, example: "20"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getTitle(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getMax(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getValue(): NumberArgument {
        return $this->getArguments()[3];
    }

    public function getColor(): StringEnumArgument {
        return $this->getArguments()[4];
    }

    public function getBarId(): StringArgument {
        return $this->getArguments()[5];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getTitle()->getString($source);
        $max = $this->getMax()->getFloat($source);
        $value = $this->getValue()->getFloat($source);
        $id = $this->getBarId()->getString($source);
        $color = $this->colors[$this->getColor()->getValue()] ?? BossBarColor::PURPLE;

        $player = $this->getPlayer()->getOnlinePlayer($source);

        Bossbar::add($player, $id, $title, $max, $value / $max, $color);

        yield Await::ALL;
    }
}
