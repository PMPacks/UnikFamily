<?php
namespace AntiTNT;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\entity\ExplosionPrimeEvent;

class Main extends PluginBase implements Listener{
	
	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info(TextFormat::GREEN . "Anti TNT activated!");
	}
	public function onExplosionPrime(ExplosionPrimeEvent $event){
		$event->setBlockBreaking(false);
	}
	public function onDisable() : void{
		$this->getLogger()->info(TextFormat::RED . "Plugin disabled");
	}
}
