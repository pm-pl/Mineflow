<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Location;

class LocationObjectVariable extends PositionObjectVariable {

    public function __construct(Location $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $location = $this->getLocation();
        return match ($index) {
            "yaw" => new NumberVariable($location->yaw),
            "pitch" => new NumberVariable($location->pitch),
            default => parent::getValueFromIndex($index),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getLocation(): Location {
        return $this->getValue();
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "yaw" => new DummyVariable(DummyVariable::NUMBER),
            "pitch" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }

    public function __toString(): string {
        $value = $this->getLocation();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName()." (".$value->getYaw().",".$value->getPitch().")";
    }
}