<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class BlockVariableDropdown extends VariableDropdown {

    protected string $variableType = DummyVariable::BLOCK;

    protected array $actions = [
        FlowItemIds::CREATE_BLOCK_VARIABLE,
        FlowItemIds::GET_BLOCK,
        FlowItemIds::GET_TARGET_BLOCK,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.block", $variables, [
            DummyVariable::BLOCK,
        ], $default, $optional);
    }
}