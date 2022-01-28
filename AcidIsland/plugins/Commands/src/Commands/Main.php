<?php

namespace Commands;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use muqsit\invmenu\{InvMenu, InvMenuHandler};
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Effect;

class Main extends PluginBase implements Listener{

public function onEnable(){
$this->getLogger()->info(TextFormat::GREEN . "Command By PIG");
}

public function onDisable(){
$this->getLogger()->info(TextFormat::RED. "Command By PIG!");
}

public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{

switch($cmd->getName()){

case "heal":
		if($sender instanceof Player){
			if($sender->hasPermission("heal.command")){
				$sender->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Bạn đã hồi thanh máu.");
				$sender->setHealth(20);
			}
		}
		break;

	case "feed":
		if($sender instanceof Player){
			if($sender->hasPermission("feed.command")){
				$sender->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Bạn đã hồi thanh thức ăn.");
				$sender->setFood(20);
			}
		}
		break;
		
       case "clear":
		if($sender instanceof Player){
			if($sender->hasPermission("clear.command")){
				$sender->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Kho đồ của bạn đã mất.");
                                $sender->getInventory()->clearAll();
				$sender->getArmorInventory()->clearAll();
				$sender->removeAllEffects();
			}
		}
		break;

}
return true;
}
}
				  
