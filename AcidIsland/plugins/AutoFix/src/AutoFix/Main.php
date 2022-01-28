<?php

namespace AutoFix;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\network\mcpe\protocol\OnScreenTextureAnimationPacket;

class Main extends PluginBase implements Listener {
    public $tag = "§l§3●§e ";
public $players = [];
	
public function onEnable() {
$this->runTask();
}
public function runTask()
    {
             $period = 20; //5s
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $why_we_need_this): void {
  foreach($this->getServer()->getOnlinePlayers() as $player) {
if(isset($this->players[strtolower($player->getName())])) {
$inventory = $player->getInventory();
$item = $inventory->getItemInHand();
$item->setDamage(0);
$inventory->setItemInHand($item);
                    }
            }
        }), $period);
    }
	
public function onCommand(CommandSender $sender, Command $cmd, String $label, array $array): bool {
if(!$sender instanceof Player) {
$sender->sendMessage("§e༺§cVui Lòng Sử Dụng Lệnh Trong Trò Chơi§e༻");
return false;
}

$name = strtolower($sender->getName());
if($cmd->getName() == "autofix") {
if(isset($this->players[$name])) {
unset($this->players[$name]);
$this->showOnScreenAnimation($sender, 3);
$sender->sendMessage($this->tag . "Bạn đã tắt AutoFix");
return true;
}else{
$this->players[$name] = true;
$this->showOnScreenAnimation($sender, 3);
$sender->sendMessage($this->tag . "Bạn đã bật AutoFix");
return true;
	}
}else{
return false;
        }
    }
     public function showOnScreenAnimation($player, int $effectId)
    {
        $packet = new OnScreenTextureAnimationPacket();
        $packet->effectId = $effectId;
        $player->sendDataPacket($packet);
    }
}