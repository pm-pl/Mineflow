<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SendInputForm extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private bool $resendOnClose = false;

    public function __construct(
        string         $player = "",
        private string $formText = "",
        private string $resultName = "input"
    ) {
        parent::__construct(self::SEND_INPUT, FlowItemCategory::FORM);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "text", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getFormText(), $this->getResultName()];
    }

    public function setFormText(string $formText): void {
        $this->formText = $formText;
    }

    public function getFormText(): string {
        return $this->formText;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->formText !== "" and $this->resultName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $text = $source->replaceVariables($this->getFormText());
        $resultName = $source->replaceVariables($this->getResultName());
        $player = $this->getOnlinePlayer($source);

        yield from Await::promise(function ($resolve) use($source, $player, $text, $resultName) {
            $this->sendForm($source, $player, $text, $resultName, $resolve);
        });
    }

    private function sendForm(FlowItemExecutor $source, Player $player, string $text, string $resultName, callable $callback): void {
        (new CustomForm($text))
            ->setContents([
                new Input($text, "", "", true),
            ])->onReceive(function (Player $player, array $data) use ($source, $resultName, $callback) {
                $variable = new StringVariable($data[0]);
                $source->addVariable($resultName, $variable);
                $callback();
            })->onClose(function (Player $player) use ($source, $text, $resultName, $callback) {
                if ($this->resendOnClose) $this->sendForm($source, $player, $text, $resultName, $callback);
            })->show($player);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.form.resultVariableName", "input", $this->getResultName(), true),
            new ExampleInput("@action.input.form.text", "aieuo", $this->getFormText(), true), // TODO: placeholder, default
            new Toggle("@action.input.form.resendOnClose", $this->resendOnClose),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setResultName($content[1]);
        $this->setFormText($content[2]);
        $this->resendOnClose = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getResultName(), $this->getFormText(), $this->resendOnClose];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
