<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\Calculate;

class CalculateCos extends Calculate implements FlowItemAlias {

    public function __construct(string $value = "", string $resultName = "result") {
        parent::__construct($value, self::CALC_COS, $resultName);
    }

    public function getId(): string {
        return self::CALCULATE_COS;
    }
}