<?php

namespace MenuAuto;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\TextFormat as C;
use jojoe77777\FormAPI\{SimpleForm, CustomForm};

class Main extends PluginBase implements Listener {

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->getLogger()->info("MENU TECHNOLOGY ON");
	}
	
	public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool {
		switch($command->getName()){
			case "menu":
			if($player instanceof Player){
			    $this->mainform($player);
			} else {
				$player->sendMessage("§cYou cannot use this command in Console !");
					return true;
			}
			break;
		}
	    return true;
	}
	public function mainform(Player $s){
		$f = new SimpleForm(function (Player $s, $data){
			if(!isset($data)){
				return false;
			}
			switch ($data){
				case 0:
				$cmd = "is";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 1:
				$this->Features($s);
				break;
				case 2:
				$this->Warps($s);
				break;
				case 3:
				$this->XuMarket($s);
				break;
				case 4:
				$this->CoinMarket($s);
				break;
				case 5:
				$this->GemMarket($s);
				break;
			}
		});
		$f->setTitle("§l§6Menu");
		$f->addButton("§l§3●§2 Island §3●");
		$f->addButton("§l§3●§2 Kho Tính Năng §3●");
		$f->addButton("§l§3●§2 Các Khu Vực §3●");
		$f->addButton("§l§3●§2 Cửa Hàng Xu §3●");
		$f->addButton("§l§3●§2 Cửa Hàng Coin §3●");
		$f->addButton("§l§3●§2 Cửa Hàng Gem §3●");
		$s->sendForm($f);
		return $f;
	}

	public function Features(Player $sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createSimpleForm(function (Player $sender, ?int $data = null){
		$result = $data;
		if($result === null){
			return $this->mainform($sender);
		    }
			switch($result){
				case 0:
				$cmd = "autofeed";
				$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
				break;
				case 1:
				$cmd = "autofix";
				$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
				break;
				 case 2:
				$cmd = "eternity";
				$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
				break;
				 case 3:
				$cmd = "kingofblock";
				$this->getServer()->getCommandMap()->dispatch($sender, $cmd);
				break;
                  case 4:
                    $cmd = "oresystem";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                  break;
                  case 5:
                    $cmd = "wing";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                    break;
                     case 6:
                    $cmd = "nhiemvu";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                    break;
                    case 7:
                    $cmd = "ssp";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                    break;
                    case 8:
                    $cmd = "wing";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                    break;
                    case 9:
                    $cmd = "danhhieu";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                    break;
                    case 10:
                    $cmd = "dungeon";
                    $this->getServer()->getCommandMap()->dispatch($sender, $cmd);
                    break;
				
			}
		});
		$form->setTitle("§l§6Kho Tính Năng");
		$form->setContent("§l§3●§e Các tính năng của máy chủ: ");
		$form->addButton("§l§3●§2 AutoFeed §3●");
		$form->addButton("§l§3●§2 AutoFix §3●");
		$form->addButton("§l§3●§2 Eternity §3●");
		$form->addButton("§l§3●§2 KingOfBlock §3●");
		$form->addButton("§l§3●§2 Hệ Thống Quặng §3●");
		$form->addButton("§l§3●§2 Nhiệm Vụ §3●");
		$form->addButton("§l§3●§2 SeasonPass §3●");
		$form->addButton("§l§3●§2 Hệ Thống Cánh §3●");
		$form->addButton("§l§3●§2 Danh Hiệu §3●");
		$form->addButton("§l§3●§2 Dungeon §3●");
		$form->sendToPlayer($sender);
			return $form;
	}

	public function Warps(Player $s){
		$f = new SimpleForm(function (Player $s, $data){
			if(!isset($data)){
				return $this->mainform($s);
			}
			switch ($data){
				case 0:
				$cmd = "warp boss";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 1:
				$cmd = "warp pvp";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 2:
				$cmd = "warp hotblock";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 3:
				$cmd = "warp trade";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 4:
				$cmd = "warp donate";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 5:
				$cmd = "warp thepit";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
			}
		});
		$f->setTitle("§l§6Các Khu Vực");
		$f->addButton("§l§3●§2 Boss §3●");
		$f->addButton("§l§3●§2 PvP §3●");
		$f->addButton("§l§3●§2 HotBlock §3●");
		$f->addButton("§l§3●§2 Trade §3●");
		$f->addButton("§l§3●§2 Donate §3●");
		$f->addButton("§l§3●§2 ThePit §3●");
		$s->sendForm($f);
		return $f;
	}

	public function XuMarket(Player $s){
		$f = new SimpleForm(function (Player $s, $data){
			if(!isset($data)){
				return $this->mainform($s);
			}
			switch ($data){
				case 0:
				$cmd = "ah";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 1:
				$cmd = "muacoin";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 2:
				$cmd = "chuyensinh";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 3:
				$cmd = "shop";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 4:
				$cmd = "bank";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 5:
				$cmd = "mualong";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
			}
		});
		$f->setTitle("§l§6Cửa Hàng Xu");
		$f->addButton("§l§3●§2 Chợ Đấu Giá §3●");
		$f->addButton("§l§3●§2 Mua Coin §3●");
		$f->addButton("§l§3●§2 Chuyển Sinh §3●");
		$f->addButton("§l§3●§2 Shop §3●");
		$f->addButton("§l§3●§2 Ngân Hàng §3●");
		$f->addButton("§l§3●§2 Mua Lồng Spawner §3●");
		$s->sendForm($f);
		return $f;
	}
	public function CoinMarket(Player $s){
		$f = new SimpleForm(function (Player $s, $data){
			if(!isset($data)){
				return $this->mainform($s);
			}
			switch ($data){
				case 0:
				$cmd = "muangoc";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 1:
				$cmd = "muavip";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 2:
				$cmd = "buyrank";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 3:
				$cmd = "buykey";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 4:
				$cmd = "buycmd";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 5:
				$cmd = "wing";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 6:
				$cmd = "blackshop";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
			}
		});
		$f->setTitle("§l§6Cửa Hàng Coin");
		$f->addButton("§l§3●§2 Khảm Ngọc §3●");
		$f->addButton("§l§3●§2 Mua Vip §3●");
		$f->addButton("§l§3●§2 Thuê Rank §3●");
		$f->addButton("§l§3●§2 Mua Key §3●");
		$f->addButton("§l§3●§2 Mua Lệnh §3●");
		$f->addButton("§l§3●§2 Mua Cánh §3●");
		$f->addButton("§l§3●§2 BlackShop §3●");
		$s->sendForm($f);
		return $f;
	}

	public function GemMarket(Player $s){
		$f = new SimpleForm(function (Player $s, $data){
			if(!isset($data)){
				return $this->mainform($s);
			}
			switch ($data){
				case 0:
				$cmd = "napthe";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 1:
				$cmd = "topnapthe";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 2:
				$cmd = "ssp";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
				case 3:
				$cmd = "shopgem";
				$this->getServer()->getCommandMap()->dispatch($s, $cmd);
				break;
			}
		});
		$f->setTitle("§l§6Cửa Hàng Gem");
		$f->addButton("§l§3●§2 Nạp Thẻ §3●");
		$f->addButton("§l§3●§2 Top Nạp Thẻ §3●");
		$f->addButton("§l§3●§2 SeasonPass §3●");
		$f->addButton("§l§3●§2 Ngọc Hiếm §3●");
		$s->sendForm($f);
		return $f;
	}
}