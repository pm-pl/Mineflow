<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\formAPI\element\Element;

abstract class FlowItemArgument implements \JsonSerializable {

    public function __construct(
        private readonly string $name,
        private string          $description = "",
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function description(string $description): static {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string {
        return $this->description;
    }

    abstract public function isValid(): bool;

    /**
     * @param array $variables
     * @return Element[]
     */
    abstract public function createFormElements(array $variables): array;

    /**
     * @param mixed ...$data
     * @return void
     * @throws InvalidFormValueException
     */
    abstract public function handleFormResponse(mixed ...$data): void;

    abstract public function jsonSerialize(): mixed;

    abstract public function load(mixed $value): void;

    abstract public function __toString(): string;
}
