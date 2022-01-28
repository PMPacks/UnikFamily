<?php

namespace ImNotYourDev\Report\forms;

use ImNotYourDev\Report\libs\dktapps\pmforms\MenuOption;
use ImNotYourDev\Report\libs\dktapps\pmforms\MenuForm;
use ImNotYourDev\Report\Report;
use pocketmine\Player;

class AdminForm extends MenuForm
{
    public function __construct()
    {
        $title = "§l§eReportAdmin";
        $text = "§a§l➤ §r§8Đây là nơi admin duyệt đơn tố cáo!";
        $options = [
            new MenuOption("§l§0「 Danh Sách Tố Cáo 」"),
            new MenuOption("§l§0「 Thùng rác 」"),
            new MenuOption("§l§0「 Cài đặt 」"),
            new MenuOption("§l§0「 Thoát 」")
        ];
        parent::__construct($title, $text, $options, function (Player $player, $data) : void {
            if($data == 0){
                $player->sendForm(new ReportListForm());
            }elseif($data == 1){
                $player->sendForm(new RecycleBinForm());
            }elseif($data == 2){
                $player->sendMessage(Report::getInstance()->prefix . "§csoon available!");
                $player->sendForm($this);
            }else{
                $player->removeAllWindows();
            }
            return;
        });
    }
}