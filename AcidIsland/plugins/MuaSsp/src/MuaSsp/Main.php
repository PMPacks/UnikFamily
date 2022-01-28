<?php

namespace MuaSsp;

use pocketmine\{Player, Server};
use pocketmine\plugin\PluginBase;
use pocketmine\utils\{TextFormat};
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender};
use jojoe77777\FormAPI;

class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$this->gem = $this->getServer()->getPluginManager()->getPlugin("GemUI");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$player = $sender->getPlayer();
		switch ($cmd->getName()){
			case "muassp":
			if(!($sender instanceof Player)){
				$this->getLogger()->notice("Dùng lệnh trong game.");
				return true;
			}
			$this->mainForm($player);
			break;
		}
		return true;
    }
    
	public function mainForm($player){

		$form = $this->formapi->createSimpleForm(function (Player $player, $result){

			if($result === null){
				return;
			}
			switch($result){
				case 0:
				    if($player->hasPermission("royalPass.Tungsten.Perm")){
				       $player->sendMessage("§l§a【Ｓeason Ｐass】 §r§cBạn đã mua Royal Pass rồi");
				       return;
				    }
			        if($this->gem->myGem($player) >= 100){
				        $player->sendMessage("§l§a【Ｓeason Ｐass】 §r§eBạn đã mua thành công §6Royal Pass.");
				        $this->gem->reduceGem($player, 100);
				        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $player->getName() . " royalPass.Tungsten.Perm");
			        }else{
			          $player->sendMessage("§l§a【Ｓeason Ｐass】 §r§cBạn cần §100 Gem§c để mua Royal Pass này!");
			        }
				break;
				
		}
		});
		$form->setTitle("§l【 MUA SEASONPASS 】");
		$form->setContent("\n§l§7➣ Mua Season Pass để nhận đặc quyền dùng Season Pass ở §6/ssp\n\n");
		$form->addButton("§lKÍCH HOẠT ROYAL PASS\n§l§8GIÁ: 100 Ponit");
		$form->sendToPlayer($player);
		
	}
 
}