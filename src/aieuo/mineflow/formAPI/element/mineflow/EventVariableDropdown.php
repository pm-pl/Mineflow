<?php

declare(strict_types=1);

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EventVariable;

class EventVariableDropdown extends VariableDropdown {

    protected array $actions = [
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.event", $variables, [
            EventVariable::class,
        ], $default, $optional);
    }
}