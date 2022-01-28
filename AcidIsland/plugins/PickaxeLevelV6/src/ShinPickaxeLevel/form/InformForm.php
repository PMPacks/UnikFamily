<?php


namespace fcoin\form;


use ShinPickaxeLevel\libs\CustomForm;
use ShinPickaxeLevel\libs\element\Input;
use ShinPickaxeLevel\libs\element\Label;
use ShinPickaxeLevel\libs\Form;
use pocketmine\Player;

class InformForm extends CustomForm
{
    private $nextForm, $closeForm;

    public function __construct(string $title, string $message, Form $nextForm = null, Form $closeForm = null)
    {
        $labels = [];
        $messages = explode("\n", $message);
        foreach ($messages as $message) {
            $labels[] = new Label($message);
        }
        parent::__construct($title, $labels);
        $this->nextForm = $nextForm;
        $this->closeForm = $closeForm;
    }

    public function onSubmit(Player $player): ?Form
    {
        if ($this->nextForm instanceof Form) $player->sendForm($this->nextForm);
        return null;
    }

    public function onClose(Player $player): ?Form
    {
        if ($this->closeForm instanceof Form) $player->sendForm($this->closeForm);
        return null;
    }
}