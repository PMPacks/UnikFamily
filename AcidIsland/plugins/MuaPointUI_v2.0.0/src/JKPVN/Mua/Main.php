<?php
//Cấm sửa lại bất cứ chỗ nào trong plugin nhé, nếu không plugin sẽ bị lỗi
namespace JKPVN\Mua;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\{Player, Server};
use jojoe7777\FormAPI;
use onebone\economyapi\EconomyAPI;
use pocketmine\network\mcpe\protocol\OnScreenTextureAnimationPacket;
class Main extends PluginBase{
	public $tag = "§l§cMuaCoins §f↣§r ";
	
	public function onEnable(){
		$this->getServer()->getLogger()->info($this->tag . "§aPlugin MuaPointUI Đã Được Bật");
		$this->point = $this->getServer()->getPluginManager()->getPlugin("CoinAPI");
		$this->money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
	}
	
	public function onLoad(): void{
		$this->getServer()->getLogger()->notice("Plugin Code VH 99℅ By JakayPluginVN");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		switch($cmd->getName()){
			case "muacoin":
			if(!($sender instanceof Player)){
				$this->getLogger()->notice("use in-game");
				return true;
			}
			$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$form = $api->createSimpleForm(Function (Player $sender, $data){
				
				$result = $data;
				if ($result == null){
				}
				switch ($result) {
					case 0:
					$sender->sendMessage("");
					break;
					case 1:
					$this->information($sender);
					break;
					case 2:
					$this->doiPoint($sender);
					break;
				}
			});
			
			$form->setTitle("§l§6MuaPoint");
			$form->setContent("§l§3●§e Đây là cửa hàng đổi point của máy chủ victory");
			$form->addButton("§l§3●§e Exit §3●", 0);
			$form->addButton("§l§3●§e Thông tin §3", 1);
			$form->addButton("§l§3●§e Mua Coins §3●", 2);
 
			$form->sendToPlayer($sender);
		}
		return true;
	}
	
	public function information($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(Function (Player $sender, $data){
		});
		$form->setTitle("§l§6Cách dùng");
		$form->addLabel("§f↣§d Sau đây là thông tin và cách dùng muacoins");
		$form->addLabel("§f↣§d Lưu ý: đổi từ point qua xu -50 %");
		$form->addLabel("§f↣§d Vui lòng nhập số point cần đổi vào khung bên dưới");
		$form->addLabel("§f↣§d Giá quy đổi 200k Xu = 1 Coins");
 
		$form->sendToPlayer($sender);
	}
	
	public function doiPoint($sender){
		$form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function(Player $sender, $data){
			if($data == null){
				$sender->sendMessage("§l§cShopCoins §f↣§r§a Cảm ơn bạn đã tham quan ShopPoint !");
				return;
			}
			$data[0] >= 1;
			$tien = $this->money->myMoney($sender);
			if(!isset($data[0]) || !is_numeric($data[0])){
				$sender->sendMessage($this->tag . "§cVui lòng Mua Coins bằng số");
			    return false;
			}
			if($tien >= $data[0]*200000){
				$sender->sendMessage($this->tag . "§aBạn đã mua §c" . $data[0] . "§e Coins§a thành công!");
				$this->showOnScreenAnimation($sender, 10);
				$this->money->reduceMoney($sender, $data[0]*200000);
				$this->point->addCoin($sender, $data[0]);
			}else{
				$sender->sendMessage($this->tag . "§cBạn không đủ Money để đổi Coins!");
				return true;
			}
		});
		$form->setTitle("§l§6MuaCoins");
		$form->addInput("§l§f•§a Ghi số Coins cần Mua", "§f0");
		$form->sendToPlayer($sender);
	}
 public function showOnScreenAnimation($player, int $effectId)
    {
        $packet = new OnScreenTextureAnimationPacket();
        $packet->effectId = $effectId;
        $player->sendDataPacket($packet);
    }
}