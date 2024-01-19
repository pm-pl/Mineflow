<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\form;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;

class SendConfirmForm extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $player = "", string $title = "", string $formText = "", string $yes = "", string $no = "", string $resultName = "result") {
        parent::__construct(self::SEND_CONFIRM_FORM, FlowItemCategory::FORM);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("title", $title)->example("aieuo"),
            StringArgument::create("text", $formText)->example("aieuo"),
            StringArgument::create("yes", $yes)->example("Yes"),
            StringArgument::create("no", $no)->example("No"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("input"),
            BooleanArgument::create("resend on close", false, "@action.input.form.resendOnClose"),
        ]);
    }

    public function getDescription(): string {
        return $this->getName();
    }

    public function getDetail(): string {
        return <<<END
            §7---§f {$this->getName()}({$this->getFormTitle()}) §7---§f
            {$this->getFormText()}
            text_yes: {$this->getYesText()}
            text_no: {$this->getNoText()}
            §7-----------------------------------§f
            END;
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getFormTitle(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getFormText(): StringArgument {
        return $this->getArguments()[2];
    }

    public function getYesText(): StringArgument {
        return $this->getArguments()[3];
    }

    public function getNoText(): StringArgument {
        return $this->getArguments()[4];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[5];
    }

    public function getResendOnClose(): BooleanArgument {
        return $this->getArguments()[6];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getFormTitle()->getString($source);
        $text = $this->getFormText()->getString($source);
        $yes = $this->getYesText()->getString($source);
        $no = $this->getNoText()->getString($source);
        $resultName = $this->getResultName()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $variable = yield from Await::promise(function ($resolve) use ($player, $title, $text, $yes, $no) {
            $this->sendForm($player, $title, $text, $yes, $no, $resolve);
        });

        $source->addVariable($resultName, $variable);
    }

    private function sendForm(Player $player, string $title, string $text, string $yes, string $no, callable $callback): void {
        (new ModalForm($title))
            ->setContent($text)
            ->setButton1($yes)
            ->setButton2($no)
            ->onReceive(function (Player $player, bool $data) use ($callback) {
                $callback(new BooleanVariable($data));
            })->onClose(function (Player $player) use ($title, $text, $yes, $no, $callback) {
                if ($this->getResendOnClose()->getBool()) {
                    $this->sendForm($player, $title, $text, $yes, $no, $callback);
                } else {
                    $callback(new BooleanVariable(false));
                }
            })->show($player);
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(BooleanVariable::class)
        ];
    }
}