<?php

declare(strict_types=1);

namespace YTBJero\an;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task; 
use pocketmine\Server;
class AlwaysNightTimer extends Task {
        public function onRun($currentTick){
                Server::getInstance()->getDefaultLevel()->setTime(14000); // set to noon
                }
        }