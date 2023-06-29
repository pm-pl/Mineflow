<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use SOFe\AwaitGenerator\Await;

class CreateScoreboardVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private array $displayTypes = [Scoreboard::DISPLAY_SIDEBAR, Scoreboard::DISPLAY_LIST, Scoreboard::DISPLAY_BELOWNAME];

    private StringArgument $boardId;
    private StringArgument $displayName;
    private StringArgument $variableName;

    public function __construct(
        string $boardId = "",
        string $displayName = "",
        private string $displayType = Scoreboard::DISPLAY_SIDEBAR,
        string $variableName = "board"
    ) {
        parent::__construct(self::CREATE_SCOREBOARD_VARIABLE, FlowItemCategory::SCOREBOARD);

        $this->variableName = new StringArgument("result", $variableName, "@action.form.resultVariableName", example: "board");
        $this->boardId = new StringArgument("id", $boardId, example: "aieuo");
        $this->displayName = new StringArgument("displayName", $displayName, example: "auieo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["result", "id", "displayName", "type"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->boardId->get(), $this->displayName->get(), $this->getDisplayType()];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function getBoardId(): StringArgument {
        return $this->boardId;
    }

    public function getDisplayName(): StringArgument {
        return $this->displayName;
    }

    public function setDisplayType(string $displayType): void {
        $this->displayType = $displayType;
    }

    public function getDisplayType(): string {
        return $this->displayType;
    }

    public function isDataValid(): bool {
        return $this->variableName->isValid() and $this->boardId->isValid() and $this->displayName->isValid() and in_array($this->displayType, $this->displayTypes, true);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $this->variableName->getString($source);
        $id = $this->boardId->getString($source);
        $displayName = $this->displayName->getString($source);
        $type = $this->getDisplayType();

        $scoreboard = new Scoreboard($type, $id, $displayName);

        $variable = new ScoreboardVariable($scoreboard);
        $source->addVariable($variableName, $variable);

        yield Await::ALL;
        return $this->variableName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->boardId->createFormElement($variables),
            $this->displayName->createFormElement($variables),
            new Dropdown("@action.createScoreboard.form.type", $this->displayTypes, array_search($this->getDisplayType(), $this->displayTypes, true)),
            $this->variableName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(2, fn($value) => $this->displayTypes[$value]);
            $response->rearrange([3, 0, 1, 2]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->boardId->set($content[1]);
        $this->displayName->set($content[2]);
        $this->setDisplayType($content[3]);
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->boardId->get(), $this->displayName->get(), $this->getDisplayType()];
    }

    public function getAddingVariables(): array {
        return [
            $this->variableName->get() => new DummyVariable(ScoreboardVariable::class, $this->displayName->get())
        ];
    }
}
