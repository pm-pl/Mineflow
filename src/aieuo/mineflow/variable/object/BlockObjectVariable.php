<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\block\Block;

class BlockObjectVariable extends PositionObjectVariable {

    public function __construct(private Block $block, ?string $str = null) {
        parent::__construct($block->getPosition(), $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $block = $this->getBlock();
        return match ($index) {
            "name" => new StringVariable($block->getName()),
            "id" => new NumberVariable($block->getId()),
            "damage" => new NumberVariable($block->getMeta()),
            "item" => new ItemObjectVariable($block->getPickedItem()),
            default => parent::getValueFromIndex($index),
        };
    }

    public function getBlock(): Block {
        return $this->block;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(DummyVariable::STRING),
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "damage" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }

    public function __toString(): string {
        $value = $this->getBlock();
        return (string)$value;
    }
}