<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\economy\Economy;

class OverMoney extends TypeMoney {

    protected $id = self::OVER_MONEY;

    protected $name = "condition.overMoney.name";
    protected $detail = "condition.overMoney.detail";

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    public function execute(?Entity $target, Recipe $origin): bool {
        $this->throwIfInvalidNumber($target);

        if (!Economy::isPluginLoaded()) {
            throw new \UnexpectedValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $amount = $origin->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount);

        $myMoney = Economy::getPlugin()->getMoney($target->getName());
        return $myMoney >= (int)$amount;
    }
}