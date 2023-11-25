<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\formAPI\element\Dropdown;
use function count;

class IntEnumArgument extends FlowItemArgument {

    /**
     * @param string $name
     * @param int $value
     * @param string[] $keys
     * @param string $description
     */
    public function __construct(
        string                 $name,
        private int            $value = 0,
        private readonly array $keys = [],
        string                 $description = "",
    ) {
        parent::__construct($name, $description);
    }

    public function value(int $enumValue): self {
        $this->value = $enumValue;
        return $this;
    }

    public function getEnumValue(): int {
        return $this->value;
    }

    public function getEnumKey(): string {
        return $this->keys[$this->getEnumValue()] ?? "";
    }

    public function isValid(): bool {
        return $this->getEnumValue() >= 0 and $this->getEnumValue() < count($this->keys);
    }

    public function createFormElements(array $variables): array {
        return [
            new Dropdown($this->getDescription(), $this->keys, $this->getEnumValue())
        ];
    }

    /**
     * @param array{0: int} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($data[0]);
    }

    public function jsonSerialize(): int {
        return $this->getEnumValue();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        return $this->getEnumKey();
    }
}
