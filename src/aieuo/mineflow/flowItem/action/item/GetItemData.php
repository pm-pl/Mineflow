<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;

class GetItemData extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;

    protected string $id = self::GET_ITEM_DATA;

    protected string $name = "action.getItemData.name";
    protected string $detail = "action.getItemData.detail";
    protected array $detailDefaultReplace = ["item", "key"];

    protected string $category = FlowItemCategory::ITEM;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        string         $item = "",
        private string $key = "",
        private string $resultName = "data",
    ) {
        $this->setItemVariableName($item);
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "" and $this->getKey() !== "" and $this->getResultName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getItemVariableName(), $this->getKey()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);
        $key = $source->replaceVariables($this->getKey());
        $resultName = $source->replaceVariables($this->getResultName());

        $tags = $item->getNamedTag();
        $tag = $tags->getTag($key);
        if ($tag === null) {
            throw new InvalidFlowValueException(Language::get("action.getItemData.tag.not.exists", [$key]));
        }

        $variable = Main::getVariableHelper()->tagToVariable($tag);
        $source->addVariable($resultName, $variable);

        yield true;
        return $this->getItemVariableName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setItemData.form.key", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setResultName($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getKey(), $this->getResultName()];
    }
}