<?php

namespace ChoCanhUI\ChoCanh;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender, CommandExecutor, ConsoleCommandSender};
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\{Player, Server};
use jojoe7777\FormAPI;

class Main extends PluginBase{
	
	public function onEnable(){
		$this->getServer()->getLogger()->info(" §l§aEnable MuaDanhHieu System....");
		$this->coin = $this->getServer()->getPluginManager()->getPlugin("CoinAPI");
	}
	
	public function onLoad(): void{
		$this->getServer()->getLogger()->notice("Loading Data.....");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch($cmd->getName()){
			case "muadanhhieu":
			if(!($sender instanceof Player)){
				$this->getLogger()->notice("Please Dont Use that command in here.");
				return true;
			}
			$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$form = $api->createSimpleForm(Function (Player $sender, $data){
				
				$result = $data;
				if ($result === null){
				}
				switch ($result) {
					case 0:
					$this->danhhieu0($sender);
					break;
					case 1:
					$this->danhhieu1($sender);
					break;
					case 2:
					$this->danhhieu2($sender);
					break;
					case 3:
					$this->danhhieu3($sender);
					break;
					case 4:
					$this->danhhieu4($sender);
					break;
					case 5:
					$this->danhhieu5($sender);
					break;
				}
			});
			
			$form->setTitle("§l§6♦ §dMua Danh Hiệu §l§6♦");
			$form->addButton("§l§3● §cＦＡ §3●\n§d【§660 Coin§d】§r");
			$form->addButton("§l§3● §cYOᑌ§dTᑌ§bᗷE §3●\n§d【§6100 Coin§d】§r");	
			$form->addButton("§l§3● §dʟovᴇʀ §3●\n§d【§660 Coin§d】§r");
			$form->addButton("§l§3● §bʙo§dss §3●\n§d【§6100 Coin§d】§r");
			$form->addButton("§l§3● §aʙà§bтâɴ§cvʟoԍ §3●\n§d【§6100 Coin§d】§r");	
			$form->addButton("§l§3● §dɴнạт §3●\n§d【§6100 Coin§d】§r");
			$form->sendToPlayer($sender);
			break;
		}
		return true;
	}
	
	public function danhhieu0($sender){
			if($sender->hasPermission('danhhieu.0')){
			   $sender->sendMessage("§l§3● §eBạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->coin->myCoin($sender) >= 60){
				$sender->sendMessage("§l§3● §eBạn đã mua thành công danh hiệu §cＦＡ");
				$this->coin->reduceCoin($sender, 60);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.0");
			}else{
				$sender->sendMessage("§l§3● §eBạn cần §c60 coin§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu1($sender){
			if($sender->hasPermission('danhhieu.1')){
			   $sender->sendMessage("§l§3● §eBạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->coin->myCoin($sender) >= 60){
				$sender->sendMessage("§l§3● §eBạn đã mua thành công danh hiệu §cYOᑌ§dTᑌ§bᗷE");
				$this->coin->reduceCoin($sender, 60);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.1");
			}else{
				$sender->sendMessage("§l§3● §eBạn cần §c60 coin§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu2($sender){
			if($sender->hasPermission('danhhieu.2')){
			   $sender->sendMessage("§l§3● §eBạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->coin->myCoin($sender) >= 100){
				$sender->sendMessage("§l§3● §eBạn đã mua thành công danh hiệu §dʟovᴇʀ");
				$this->coin->reduceCoin($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.2");
			}else{
				$sender->sendMessage("§l§3● §eBạn cần §c100 coin§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu3($sender){
			if($sender->hasPermission('danhhieu.3')){
			   $sender->sendMessage("§l§3● §eBạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->coin->myCoin($sender) >= 60){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §bʙo§dss");
				$this->coin->reduceCoin($sender, 60);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.3");
			}else{
				$sender->sendMessage("§l§3● §eBạn cần §c60 coin§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu4($sender){
			if($sender->hasPermission('danhhieu.4')){
			   $sender->sendMessage("§l§3● §eBạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->coin->myCoin($sender) >= 100){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §aʙà§bтâɴ§cvʟoԍ");
				$this->coin->reduceCoin($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.4");
			}else{
				$sender->sendMessage("§l§3● §eBạn cần §c100 coin§e để mua danh hiệu này!");
				return true;
			}
	}
	
	public function danhhieu5($sender){
			if($sender->hasPermission('danhhieu.5')){
			   $sender->sendMessage("§l§3● §eBạn đã sở hữu danh hiệu này rồi!");
			   return false;
			}
			if($this->coin->myCoin($sender) >= 100){
				$sender->sendMessage("§l§6[§bSky§aBlock(Bin)§6]§e Bạn đã mua thành công danh hiệu §dɴнạт");
				$this->coin->reduceCoin($sender, 100);
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm " .  $sender->getName() . " danhhieu.5");
			}else{
				$sender->sendMessage("§l§3● §eBạn cần §c100 coin§e để mua danh hiệu này!");
				return true;
			}
	}
}