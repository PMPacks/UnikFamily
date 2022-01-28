<?php

declare(strict_types=1);

namespace YTBJero\an;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask; 
use pocketmine\Server;
class Main extends PluginBase {
	public function onEnable(){
		$this->getScheduler()->scheduleRepeatingTask(new AlwaysNightTimer($this), 1);
		}
	}