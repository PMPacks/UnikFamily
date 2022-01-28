<?php

namespace online;

use pocketmine\scheduler\Task;
use online\Main;

class TimeTask extends Task{


    public function __construct(Main $plugin){

        $this->plugin = $plugin;
    }
    public function onRun($currentTick){
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player){
            $this->plugin->create($player);
            $this->plugin->addData($player);
        }
    }
}

    