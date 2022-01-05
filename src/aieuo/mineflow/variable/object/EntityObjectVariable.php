<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;

class EntityObjectVariable extends PositionObjectVariable {

    public function __construct(private Entity $entity, ?string $str = null) {
        parent::__construct($this->entity->getPosition(), $str);
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variable = parent::getValueFromIndex($index);
        if ($variable !== null) return $variable;

        $entity = $this->getEntity();
        switch ($index) {
            case "id":
                return new NumberVariable($entity->getId());
            case "saveId":
                try {
                    return new StringVariable(EntityFactory::getInstance()->getSaveId($entity::class));
                } catch (\InvalidArgumentException) {
                    return new StringVariable("");
                }
            case "nameTag":
                return new StringVariable($entity->getNameTag());
            case "health":
                return new NumberVariable($entity->getHealth());
            case "maxHealth":
                return new NumberVariable($entity->getMaxHealth());
            case "yaw":
                return new NumberVariable($entity->getLocation()->getYaw());
            case "pitch":
                return new NumberVariable($entity->getLocation()->getPitch());
            case "direction":
                return new NumberVariable($entity->getHorizontalFacing());
            case "onGround":
                return new BoolVariable($entity->isOnGround());
            default:
                return null;
        }
    }

    public function getEntity(): Entity {
        return $this->entity;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "id" => new DummyVariable(DummyVariable::NUMBER),
            "saveId" => new DummyVariable(DummyVariable::STRING),
            "nameTag" => new DummyVariable(DummyVariable::STRING),
            "health" => new DummyVariable(DummyVariable::NUMBER),
            "maxHealth" => new DummyVariable(DummyVariable::NUMBER),
            "yaw" => new DummyVariable(DummyVariable::NUMBER),
            "pitch" => new DummyVariable(DummyVariable::NUMBER),
            "direction" => new DummyVariable(DummyVariable::NUMBER),
        ]);
    }

    public function __toString(): string {
        $value = $this->getEntity();
        return $value->getNameTag();
    }
}