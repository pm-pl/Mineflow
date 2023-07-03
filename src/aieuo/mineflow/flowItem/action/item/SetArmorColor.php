<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\ItemArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\color\Color;
use pocketmine\item\Armor;
use SOFe\AwaitGenerator\Await;

class SetArmorColor extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $item = "", string $red = "", string $green = "", string $blue = "") {
        parent::__construct(self::SET_ARMOR_COLOR, FlowItemCategory::ITEM);

        $this->setArguments([
            new ItemArgument("armor", $item),
            new NumberArgument("red", $red, example: "0", min: 0, max: 255),
            new NumberArgument("green", $green, example: "255", min: 0, max: 255),
            new NumberArgument("blue", $blue, example: "0", min: 0, max: 255),
        ]);
    }

    public function getArmor(): ItemArgument {
        return $this->getArguments()[0];
    }

    public function getRed(): NumberArgument {
        return $this->getArguments()[1];
    }

    public function getGreen(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getBlue(): NumberArgument {
        return $this->getArguments()[3];
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $r = $this->getRed()->getInt($source);
        $g = $this->getGreen()->getInt($source);
        $b = $this->getBlue()->getInt($source);

        $item = $this->getArmor()->getItem($source);
        if (!($item instanceof Armor)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.setArmorColor.not.armor", [$this->getArmor()->get()]));
        }

        $item->setCustomColor(new Color($r, $g, $b));
        yield Await::ALL;
        return $this->getArmor()->get();
    }
}
