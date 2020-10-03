<?php

namespace aieuo\mineflow\variable;

class NumberVariable extends Variable implements \JsonSerializable {

    public $type = Variable::NUMBER;

    /**
     * @param int|float $value
     * @param string $name
     */
    public function __construct($value, string $name = "") {
        parent::__construct($value, $name);
    }

    /**
     * @return int|float
     */
    public function getValue() {
        return parent::getValue();
    }

    public function addition(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() + $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function subtraction(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() - $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function multiplication(NumberVariable $var, string $resultName = "result"): NumberVariable {
        $result = $this->getValue() * $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function division(NumberVariable $var, string $resultName = "result"): NumberVariable {
        if ($var->getValue() === 0) {
            throw new \DivisionByZeroError();
        }
        $result = $this->getValue() / $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function modulo(NumberVariable $var, string $resultName = "result"): NumberVariable {
        if ($var->getValue() === 0) {
            throw new \DivisionByZeroError();
        }
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($result, $resultName);
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable((string)$this->getValue(), $this->getName());
    }

    public function __toString() {
        return (string)$this->getValue();
    }

    public function jsonSerialize() {
        return [
            "name" => $this->getName(), "type" => $this->getType(), "value" => $this->getValue(),
        ];
    }

    public static function fromArray(array $data): ?Variable {
        if (!isset($data["value"])) return null;
        return new self((float)$data["value"], $data["name"] ?? "");
    }
}