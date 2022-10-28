<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\string;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class StringLength extends FlowItem {
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        private string $value = "",
        private string $resultName = "length"
    ) {
        parent::__construct(self::STRING_LENGTH, FlowItemCategory::STRING);
    }

    public function getDetailDefaultReplaces(): array {
        return ["string", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getValue(), $this->getResultName()];
    }

    public function setValue(string $value1): self {
        $this->value = $value1;
        return $this;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getValue() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $value = $source->replaceVariables($this->getValue());
        $resultName = $source->replaceVariables($this->getResultName());

        $length = mb_strlen($value);
        $source->addVariable($resultName, new NumberVariable($length));

        yield Await::ALL;
        return $length;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.strlen.form.value", "aieuo", $this->getValue(), true),
            new ExampleInput("@action.form.resultVariableName", "length", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setValue($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getValue(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(NumberVariable::class)
        ];
    }
}
