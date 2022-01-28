<?php

namespace ShinPickaxeLevel\provider;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use ShinPickaxeLevel\Main as PICK;

class DataProvider{

	private $plugin;
	public function __construct(PICK $plugin){
		$this->plugin = $plugin;
		if(!file_exists($this->plugin->getDataFolder() . "players/")){
			@mkdir($this->plugin->getDataFolder() . "players/");
		}
	}
	public function getPlayer(Player $player){
		$name = trim(strtolower($player->getName()));
		if($name === ""){
			return null;
		}
		$path = $this->plugin->getDataFolder() . "players/" . $name[0] . "/$name.yml";
		if(!file_exists($path)){
			return null;
		}else{
			$config = new Config($path, Config::YAML);
			return $config->getAll();
		}
	}
	
	public function savePlayer(Player $player, array $config){
		$name = trim(strtolower($player->getName()));
		$data = new Config($this->plugin->getDataFolder() . "players/" . $name[0] . "/$name.yml", Config::YAML);
		$config['health'] = $player->getHealth();
		$data->setAll($config);
		$data->save();
	}	
	
	public function registerPlayer(Player $player){
		$p = $player->getName();
		$this->plugin->getLogger()->notice(" Không tìm thấy dữ liệu $p ");
		$this->plugin->getLogger()->notice(" Tạo dữ liệu $p ");
		$name = trim(strtolower($player->getName()));
		@mkdir($this->plugin->getDataFolder() . "players/" . $name[0] . "/");
		$data = new Config($this->plugin->getDataFolder() . "players/" . $name[0] . "/$name.yml", Config::YAML);
		$data->set("level", 1);
		$data->set("exp", 0);
		$data->set("nextexp", 100);
		$data->set("rebirth", 1);
		$data->set("health", 20);
		$data->save();
		$this->plugin->data[$player->getName()] = $this->getPlayer($player);
		$inv = $player->getInventory();  
		$item = Item::get(278, 0, 1);
		$this->plugin->getLogger()->info($this->plugin->getNamePickaxe($player));
		$item->setCustomName($this->plugin->getNamePickaxe($player));
		$inv->addItem($item);
		$player->sendMessage("§l§c●§b Cúp đã được thêm vào túi đồ của bạn, hãy cùng đồng hành với nó lâu nhé, cúp có thể trở nên một cây cúp §evip");
		return true;
	}

}