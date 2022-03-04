<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\StringVariable;

class DeleteListVariableContentByValue extends FlowItem {

    protected string $id = self::DELETE_LIST_VARIABLE_CONTENT_BY_VALUE;

    protected string $name = "action.removeContentByValue.name";
    protected string $detail = "action.removeContentByValue.detail";
    protected array $detailDefaultReplace = ["name", "scope", "value"];

    protected string $category = FlowItemCategory::VARIABLE;

    public function __construct(
        private string $variableName = "",
        private string $variableValue = "",
        private bool   $isLocal = true
    ) {
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setValue(string $variableKey): void {
        $this->variableValue = $variableKey;
    }

    public function getValue(): string {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->variableValue !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", $this->getValue()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());

        $value = $this->getValue();
        if ($helper->isVariableString($value)) {
            $value = $source->getVariable(mb_substr($value, 1, -1)) ?? $helper->getNested(mb_substr($value, 1, -1));
            if ($value === null) {
                throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
            }
        } else {
            $value = new StringVariable($source->replaceVariables($this->getValue()));
        }

        $variable = $source->getVariable($name) ?? ($this->isLocal ? null : $helper->getNested($name));
        if ($variable === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("variable.notFound", [$name]));
        }
        if (!($variable instanceof ListVariable)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.addListVariable.error.existsOtherType", [$name, (string)$variable]));
        }

        $variable->removeValue($value, false);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.value", "auieo", $this->getValue(), true),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        return [$data[0], $data[1], !$data[2]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setValue($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getValue(), $this->isLocal];
    }
}