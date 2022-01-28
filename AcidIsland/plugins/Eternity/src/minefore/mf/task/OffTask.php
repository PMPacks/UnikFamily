<?php

namespace minefore\mf\task;

use pocketmine\scheduler\Task;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use minefore\mf\Main as Eternity;

class OffTask extends Task {
	
	public function __construct(Eternity $plugin) {
		
		$this->plugin = $plugin;	
	}
	
	public function getPlugin() {
		return $this->plugin;
	}
	
	public function onRun($currentTick) {
		if(count($this->getPlugin()->cons->getAll()) >= 1){
			foreach($this->getPlugin()->cons->getAll() as $p => $con){
				if($con == 0){
					$all = $this->getPlugin()->cons->getAll();
					unset($all[$p]);
					$this->getPlugin()->cons->setAll($all);
					$this->getPlugin()->cfg->set($p, 'off');
					if($this->getPlugin()->getServer()->getPlayer($p) !== null){
						$this->getPlugin()->getServer()->getPlayer($p)->sendMessage("§l§3●§e §cEternity§e của bạn đã hết hạn");
					}					
					continue;
				}
				if($this->getPlugin()->getServer()->getPlayer($p) !== null){
					$this->getPlugin()->getServer()->getPlayer($p)->sendMessage("§l§3●§e §cEternity§e của bạn còn§a $con phút");
				}
				$this->getPlugin()->cons->set($p, $con -1);
			}
			$this->getPlugin()->cons->save();
			$this->getPlugin()->cfg->save();
		}
	}
}