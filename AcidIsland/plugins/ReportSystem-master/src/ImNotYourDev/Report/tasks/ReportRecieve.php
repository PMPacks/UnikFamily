<?php

namespace ImNotYourDev\Report\tasks;

use ImNotYourDev\Report\Report;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class ReportRecieve extends Task
{
    public function onRun(int $currentTick)
    {
        $cfg = new Config("/reports/inf.yml", Config::YAML);
        if($cfg->get("new")){
            foreach (Report::getInstance()->getServer()->getOnlinePlayers() as $player){
                if($player->hasPermission("reportsystem.admin")){
                    $player->sendMessage(Report::getInstance()->prefix . "§cCó một đơn tố cáo mới /reportadmin để xứ lý!");
                }
            }
        }
    }
}