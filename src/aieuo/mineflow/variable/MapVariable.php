<?php

namespace aieuo\mineflow\variable;

class MapVariable extends ListVariable {

    public $type = Variable::MAP;

    public function addValue(Variable $value): void {
        $this->value[$value->getName()] = $value;
    }

    public function __toString(): string {
        if (!empty($this->getShowString())) return $this->getShowString();
        $values = [];
        foreach ($this->getValue() as $key => $value) {
            $values[] = $key.":".$value;
        }
        return "<".implode(",", $values).">";
    }
}