<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\UnknownVariable;
use function array_is_list;
use function array_key_first;
use function array_keys;
use function array_map;
use function is_int;
use function is_string;
use function iterator_to_array;

trait IteratorVariableTrait {

    public function pluck(string $index): ?Variable {
        $newValues = [];
        foreach ($this->getIterator() as $i => $value) {
            $property = $value->getProperty($index);
            if ($property === null) return null;
            $newValues[$i] = $property;
        }
        return array_is_list($newValues) ? new ListVariable($newValues) : new MapVariable($newValues);
    }

    public function first(): ?Variable {
        $values = iterator_to_array($this->getIterator());
        return $values[array_key_first($values)] ?? null;
    }

    public function last(): ?Variable {
        $values = iterator_to_array($this->getIterator());
        return $values[array_key_last($values)] ?? null;
    }

    public function firstKey(): ?Variable {
        $values = iterator_to_array($this->getIterator());
        $key = array_key_first($values);
        return $this->keyToVariable($key);
    }

    public function lastKey(): ?Variable {
        $values = iterator_to_array($this->getIterator());
        $key = array_key_last($values);
        return $this->keyToVariable($key);
    }

    public function keys(): ListVariable {
        $values = iterator_to_array($this->getIterator());
        return new ListVariable(array_map(fn($key) => $this->keyToVariable($key), array_keys($values)));
    }

    public function values(): ListVariable {
        $values = iterator_to_array($this->getIterator());
        return new ListVariable(array_values($values));
    }

    public function count(): NumberVariable {
        return new NumberVariable(count(iterator_to_array($this->getIterator())));
    }

    private function keyToVariable(string|int|null $key): Variable {
        return match (true) {
            is_string($key) => new StringVariable($key),
            is_int($key) => new NumberVariable($key),
            default => new NullVariable(),
        };
    }

    public static function registerIteratorMethods(string $class): void {
        self::registerMethod($class, "keys", new VariableMethod(
            new DummyVariable(ListVariable::class),
            fn(IteratorVariable $var) => $var->keys(),
            passVariable: true,
        ));
        self::registerMethod($class, "values", new VariableMethod(
            new DummyVariable(ListVariable::class),
            fn(IteratorVariable $var) => $var->values(),
            passVariable: true,
        ));
        self::registerMethod($class, "count", new VariableMethod(
            new DummyVariable(NumberVariable::class),
            fn(IteratorVariable $var) => $var->count(),
            passVariable: true,
        ));
        self::registerMethod($class, "first", new VariableMethod(
            new DummyVariable(UnknownVariable::class),
            fn(IteratorVariable $var) => $var->first() ?? new NullVariable(),
            passVariable: true,
        ));
        self::registerMethod($class, "last", new VariableMethod(
            new DummyVariable(UnknownVariable::class),
            fn(IteratorVariable $var) => $var->last() ?? new NullVariable(),
            passVariable: true,
        ));
        self::registerMethod($class, "first_key", new VariableMethod(
            new DummyVariable(StringVariable::class),
            fn(IteratorVariable $var) => $var->firstKey() ?? new NullVariable(),
            passVariable: true,
        ));
        self::registerMethod($class, "last_key", new VariableMethod(
            new DummyVariable(StringVariable::class),
            fn(IteratorVariable $var) => $var->lastKey() ?? new NullVariable(),
            passVariable: true,
        ));
        self::registerMethod($class, "pluck", new VariableMethod(
            new DummyVariable(ListVariable::class, "nullable"),
            fn(IteratorVariable $var, string $name) => $var->pluck($name) ?? new NullVariable(),
            passVariable: true,
        ));
    }

}