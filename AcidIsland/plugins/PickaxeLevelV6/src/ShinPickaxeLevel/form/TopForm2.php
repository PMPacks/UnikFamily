<?php


namespace ShinPickaxeLevel\form;


use ShinPickaxeLevel\Main as ShinPickaxeLevel;
use ShinPickaxeLevel\libs\CustomForm;
use ShinPickaxeLevel\libs\element\Input;
use ShinPickaxeLevel\libs\element\Label;
use ShinPickaxeLevel\libs\Form;
use pocketmine\Player;

class TopForm2 extends CustomForm
{
    private $closeForm;

    public function __construct(Player $player, int $currentPage, int $maxPage, array $content, Form $closeForm = null)
    {
		//var_dump(ShinPickaxeLevel::getInstance()->data);
        $elements = [
            new Label("§c§l● §eChuyển Sinh Của Bạn:§3 " . ShinPickaxeLevel::getInstance()->getReBirth($player)),
            new Label("§c§l● §bTrang§a $currentPage/$maxPage"),
        ];
        foreach ($content as $string) {
            $elements[] = new Label($string);
        }
        $elements[] = new Input("§l§aĐến Trang");
        parent::__construct("§l§c●§6 Xếp Hạng Chuyển Sinh§r", $elements);
        $this->closeForm = $closeForm;
    }

    public function onSubmit(Player $player): ?Form
    {
        /** @var Input $pageInput */
        $elements = $this->getAllElements();
        $pageInput = $elements[count($elements) - 1];
        $page = (int)$pageInput->getValue();
		$data = ShinPickaxeLevel::getInstance()->rb->getAll();
        ShinPickaxeLevel::sendTop($player, $data, $page, $this->closeForm);
        return null;
    }

    public function onClose(Player $player): ?Form
    {
        if ($this->closeForm instanceof Form) $player->sendForm($this->closeForm);
        return null;
    }
}