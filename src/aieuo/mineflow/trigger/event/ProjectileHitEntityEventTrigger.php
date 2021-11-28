<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\entity\ProjectileHitEntityEvent;

class ProjectileHitEntityEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("ProjectileHitEntityEvent", $subKey, ProjectileHitEntityEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var ProjectileHitEntityEvent $event */
        $variables = DefaultVariables::getEntityVariables($event->getEntityHit());
        return array_merge($variables, DefaultVariables::getEntityVariables($event->getEntity(), "projective"));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "projective" => new DummyVariable(EntityObjectVariable::class),
        ];
    }
}