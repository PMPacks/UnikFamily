<?php

namespace BrMcpeBoy\RankUP;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\level\sound\PopSound;

use pocketmine\event\Listener;

use onebone\coinapi\CoinAPI;

class main extends PluginBase implements Listener {

	public function onEnable(){

	}

	public function onCommand(CommandSender $sender, Command $cmd, String $label, Array $args) : bool {

		switch($cmd->getName()){
			case "nangrank":
			 if($sender instanceof Player){
			 	$this->rank($sender);
			 }
		}
	return true;
	}

	public function rank($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$brperm = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
		$brrank = $brperm->getUserDataMgr()->getGroup($player)->getName();
		$brcoin = CoinAPI::getInstance()->myCoin($player);
		$player->getLevel()->addSound(new PopSound($player));
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){
				case 0:
					$brperm = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
					$boss = $brperm->getGroup("Boss");
					$saiyan = $brperm->getGroup("Saiyan");
					$majin = $brperm->getGroup("Majin");
					$legendary = $brperm->getGroup("Legendary");
					$brrank = $brperm->getUserDataMgr()->getGroup($player)->getName();
					$brcoin = CoinAPI::getInstance()->myCoin($player);
					if($brrank == "Boss"){
						if($brcoin > 1200){
							CoinAPI::getInstance()->reduceCoin($player, "1200");
							$brperm->setGroup($player, $boss);
							$volume = mt_rand();
	           $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_BREAK, (int) $volume);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$boss);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							return true;
						}
						if($brcoin == 1200){
							CoinAPI::getInstance()->reducecoin($player, "1200");
							$brperm->setGroup($player, $boss);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$boss);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							 
							return true;
						}
						if($brcoin < 1200){
						    $volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_DOOR_CRASH, (int) $volume);
							$player->sendMessage("§l§b» §r§cYou dont have enough coin!");
							return true;
						}
					}
					if($brrank == "Saiyan"){
						if($brcoin > 1500){
							CoinAPI::getInstance()->reducecoin($player, "1500");
							$brperm->setGroup($player, $saiyan);
							$volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_BREAK, (int) $volume);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$saiyan);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							return true;
						}
						if($brcoin == 1500){
							CoinAPI::getInstance()->reducecoin($player, "1500");
							$brperm->setGroup($player, $saiyan);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$saiyan);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							 
							return true;
						}
						if($brcoin < 1500){
						    $volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_DOOR_CRASH, (int) $volume);
							$player->sendMessage("§l§b» §r§cYou dont have enough coin!");
							return true;
						}
					}
					if($brrank == "Majin"){
						if($brcoin > 2000){
							CoinAPI::getInstance()->reducecoin($player, "1800");
							$brperm->setGroup($player, $majin);
							$volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_BREAK, (int) $volume);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$majin);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							return true;
						}
						if($brcoin == 1800){
							CoinAPI::getInstance()->reducecoin($player, "1800");
							$brperm->setGroup($player, $majin);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$majin);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							 
							return true;
						}
						if($brcoin < 1800){
						    $volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_DOOR_CRASH, (int) $volume);
							$player->sendMessage("§l§b» §r§cYou dont have enough coin!");
							return true;
						}
					}
					if($brrank == "Legendary"){
						if($brcoin > 2000){
							CoinAPI::getInstance()->reducecoin($player, "2000");
							$brperm->setGroup($player, $legendary);
							$volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_BREAK, (int) $volume);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$legendary);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							return true;
						}
						if($brcoin == 2000){
							CoinAPI::getInstance()->reducecoin($player, "2000");
							$brperm->setGroup($player, $legendary);
							$player->sendMessage("§l§b» §r§ebạn đã được nâng cấp lên §l§a".$legendary);
							$player->addTitle("§l§e» §bUNIK §e§l«", "§6ＳＫＹＢＬＯＣＫ ＣＯ-ＯＰ");
							 
							return true;
						}
						if($brcoin < 2000){
						    $volume = mt_rand();
	                        $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_DOOR_CRASH, (int) $volume);
							$player->sendMessage("§l§b» §r§cYou dont have enough coin!");
							return true;
						}
					}
					if($brrank == "Legendary"){
						$player->sendMessage("§l§a» §r§cBạn Đã Lên Mức Rank Cao Nhất!");
						$player->addTitle("§l§e» §cUNIK §e§l«", "§6ACIDISLAND");
						return true;
					}
				break;

				case 1:
				  $this->ranklister($player);
				break;
				case 2:
                   $this->rankpremium($player);
				break;
			}
		});
		$form->setTitle("§l§a» §l§aShop Rank §a§l«");
		$form->setContent("§l§8» §r§8Chào mừng bạn đến với Shop Rank\n§l§8» §r§8Rank Của Bạn: §f" . $brrank . "\n§l§8» §r§8Coins Của Bạn: §f" . $brcoin . "");
		$form->addButton("§r§bNâng Cấp Rank\n§l§d» §r§7Nhấn Để Nâng", 0, "textures/ui/jump_boost_effect");
		$form->addButton("§r§aDanh Sách Rank\n§l§d» §r§7Nhấn Để Xem", 0, "textures/ui/controller_glyph_color");
		$form->addButton("§r§aRank §bPremium\n§l§d» §r§7Nhấn Để Xem", 0, "textures/ui/village_hero_effect");
		$form->addButton("§l§cCancel" , 0,"textures/ui/cancel");
		$form->sendToPlayer($player);
		return $form;
	}
	public function ranklister($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$volume = mt_rand();
	    $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_POP, (int) $volume);
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){
				case 0:
				  $this->ranklister($player);
				break;
			}
		});
		$form->setTitle("§l§a» §r§aDanh Sách Rank §a§l«");
		$form->setContent("\n§l§a» §aDanh Sách Rank:\n§7- §cBoss: §f1200 §eCoins\n§7- §cSaiyan: §f1500 §eCoins\n§7- §cMaster: §f1800 §eCoins\n§7- §cLengendary: §f2000 §eCoins\n");
		$form->addButton("§l§cExit",0,"textures/ui/cancel");
		$form->sendToPlayer($player);
		return $form;
	}
	public function rankpremium($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$volume = mt_rand();
	    $player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_POP, (int) $volume);
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){
				case 0:
				  $this->rankpremium($player);
				break;
			}
		});
		$form->setTitle("§l§a» §r§aRank Độc Quyền§a§l«");
		$form->setContent("\n§l§a» §r§f §aRanks §ePremium\n\n§6Donate §r: §fComing Soon\n§3Thống Trị §r: §7Coming Soon");
		$form->addButton("§l§cExit",0,"textures/ui/cancel");
		$form->sendToPlayer($player);
		return $form;
	}

}