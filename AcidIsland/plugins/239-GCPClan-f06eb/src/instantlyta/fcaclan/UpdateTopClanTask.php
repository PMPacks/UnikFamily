<?php


namespace instantlyta\fcaclan;


use pocketmine\scheduler\Task;

class UpdateTopClanTask extends Task
{
    public function onRun($currentTick)
    {
        FCAClan::getInstance()->updateTopClan();
    }
}