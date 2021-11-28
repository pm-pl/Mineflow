<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\player\PlayerToggleFlightEvent;

class PlayerToggleFlightEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("PlayerToggleFlightEvent", $subKey, PlayerToggleFlightEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerToggleFlightEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new BooleanVariable($event->isFlying());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "state" => new DummyVariable(BooleanVariable::class),
        ];
    }
}