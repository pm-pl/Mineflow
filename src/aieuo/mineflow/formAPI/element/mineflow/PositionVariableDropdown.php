<?php

declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use aieuo\mineflow\variable\object\LivingVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\PositionVariable;

class PositionVariableDropdown extends VariableDropdown {

    protected string $variableClass = PositionVariable::class;

    protected array $actions = [
        FlowItemIds::CREATE_POSITION_VARIABLE,
        FlowItemIds::GET_ENTITY_SIDE,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.position", $variables, [
            PositionVariable::class,
            LocationVariable::class,
            PlayerVariable::class,
            HumanVariable::class,
            LivingVariable::class,
            EntityVariable::class,
            BlockVariable::class,
        ], $default, $optional);
    }
}
