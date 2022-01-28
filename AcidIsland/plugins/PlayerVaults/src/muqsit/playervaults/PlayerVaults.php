<?php

declare(strict_types=1);

namespace muqsit\playervaults;

use muqsit\invmenu\InvMenuHandler;
use muqsit\playervaults\database\Database;
use muqsit\playervaults\database\Vault;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class PlayerVaults extends PluginBase{

	/** @var Database */
	private $database;

	/** @var PermissionManager */
	private $permission_manager;

	public function onEnable() : void{
		$this->initVirions();
		$this->createDatabase();
		$this->loadConfiguration();
	}

	public function onDisable() : void{
		$this->getDatabase()->close();
	}

	private function initVirions() : void{
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
	}

	private function createDatabase() : void{
		$this->saveDefaultConfig();
		$this->database = new Database($this, $this->getConfig()->get("database"));
	}

	private function loadConfiguration() : void{
		Vault::setNameFormat((string) $this->getConfig()->get("inventory-name"));

		$this->saveResource("permission-grouping.yml");
		$this->permission_manager = new PermissionManager(new Config($this->getDataFolder() . "permission-grouping.yml", Config::YAML));
	}

	public function getDatabase() : Database{
		return $this->database;
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
		if(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "§l§3●§e Lệnh này phải dùng ở player.");
			return true;
		}
		if(!$sender->isSurvival()){
			$sender->sendMessage("§l§3●§e Bạn chỉ có thể dùng lệnh này này ở chế độ sinh tồn");
			return true;
		}
		
		if(isset($args[0])){
			$number = (int) $args[0];
			if($number > 0){
				$player = $sender->getName();

				if(isset($args[1]) && strtolower($args[1]) !== strtolower($player)){
					if($sender->hasPermission("playervaults.others.view")){
						$player = $args[1];
					}else{
						$sender->sendMessage(TextFormat::RED . "§l§3●§e Bạn không có quyền để xem kho đồ của" . $args[1] . " số " . $number . ".");
						return false;
					}
				}else{
					if(!$this->permission_manager->hasPermission($sender, $number)){
						$sender->sendMessage(TextFormat::RED . "§l§3●§e Bạn không có quyền để mở kho đồ" . $number . ".");
						return false;
					}
				}

				$sender->sendMessage(TextFormat::GRAY . "§l§3●§e Đang mở kho đồ " . ($player === $sender->getName() ? "" : " " . $player . "'s") . " số " . $number . "");

				$this->getDatabase()->loadVault($player, $number, function(Vault $vault) use($sender) : void{
					if($sender->isOnline()){
						$vault->send($sender);
					}
				});
				return true;
			}
		}

		$sender->sendMessage(TextFormat::RED . "§l§3●§e Sử Dụng:§b /" . $label . " <số> " . ($sender->hasPermission("playervaults.others.view") ? "[người chơi]" : ""));
		return false;
	}
}
