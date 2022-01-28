<?php

namespace phuongaz\AuraBoss;

use pocketmine\plugin\PluginBase;
use Pocketmine\item\Item;
use pocketmine\{Player,Server};
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\NBT;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use phuongaz\AuraBoss\BossCommands;
use phuongaz\AuraBoss\Entity\ViThu;
use phuongaz\AuraBoss\Entity\BossMain;
use pocketmine\entity\Skin;
use xenialdan\apibossbar\BossBar;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\scheduler\Task;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;

use pocketmine\item\enchantment\{
	Enchantment,
	EnchantmentInstance
};
Class Boss extends PluginBase implements Listener
{
	public static $instance;
	
	public function onEnable(){
		Entity::registerEntity(BossMain::class, true);
		self::$instance = $this;
		$this->config = new Config($this->getDataFolder(). 'boss.yml', Config::YAML);
		$this->saveDefaultConfig();
		Server::getInstance()->getPluginManager()->registerEvents(new EventListeners($this), $this);
		Server::getInstance()->getCommandMap()->register("boss", new BossCommands($this));
	}

	public static function getInstance(){
		return self::$instance;
	}

	public function getAllBoss(){
		return $this->config->getAll();
	}

	public function getBossConfig(){
		return $this->config;
	}

	public function getManager(){
		return new BossManager();
	}

	public function getBossData($id){
		if(array_key_exists($id, $this->getAllBoss())){
			return $this->getBossConfig()->get($id);
		}
		$key = array_keys($this->getAllBoss())[$id];
		return $this->config->get($key);
	}

	public function getDrops($id){
		
		$drops = [];
		$items = $this->getBossData($id)['drops'];
		foreach($items as $item){
			$ex = explode(':', $item);
			$drops[] = Item::get($ex[0], $ex[1], $ex[2]);
		}
		return $drops;
	}

	public function chance(int $succes = 100){
		$percentage = mt_rand(0, 10000);
		if($percentage >= 1 && $percentage < $succes*100){
			return true;
		}
		return false;
	}

	public function getCustomDrops($id){
		if(empty($this->getBossData($id)['custom-item'])) return null;
		$drops = [];
		$items = $this->getBossData($id)['custom-item'];
		foreach(array_keys($items) as $sitem){
			$sitem = $this->getBossData($id)["custom-item"][$sitem];
			$ex = explode(":", $sitem["id"]);
			$item = Item::get($ex[0], $ex[1], $ex[2]);
			$item->setCustomName($sitem["name"]);
			$item->setLore(array($sitem["lore"]));
			foreach($sitem["enchantment"] as $ec){
				$exec = explode(":", $ec);
				$instance = new EnchantmentInstance(Enchantment::getEnchantment((int)$exec[0]), (int)$exec[1]);
				$item->addEnchantment($instance);
			}
			
			if($this->chance($sitem["chance"])){
				$drops[] = $item;				
			}
		}
		return $drops;
	}

	public function SaveBoss($key, array $data){
		if(array_key_exists($key, $this->getAllBoss())){
			return true;
		}
		$this->config->set($key, $data);
		$this->config->save();
		$this->config->reload();
	}

	public function spawnBoss($id){
		$data = $this->getBossData($id);
		$nbt = $this->makeNBT($id, $data);
		$ent = $this->makeEntity($id, $data, $nbt);
		$ent->spawnToAll();
		$skin = $this->getManager()->getSkin($data["skin"]);
		$ent->setSkin($skin);
	}

	public function makeNBT($id, $data){
		$level = $this->getServer()->getLevelByName($data["level"]);
		$pos = new Position($data["posX"], $data["posY"], $data["posZ"], $level);
		$nbt = Entity::createBaseNBT($pos);
		$nbt->setInt("BossID", $id);
		$nbt->setFloat("damage", $data['damage']);
		$nbt->setString('CustomName', $data['name']);
		$nbt->setString("skin", $data["skin"]);
		return $nbt;
	}

	public function makeEntity($id, $data, $nbt){
		$level = $this->getServer()->getLevelByName($data["level"]);
		$ent = new ViThu($level, $nbt);
		$ent = $this->setSkin($ent, $data);
		$ent->setMaxHealth($data["maxhealth"]);
		$ent->setHealth($data['maxhealth']);
		$ent->setSpeed($data['speed']);
		$ent->setScale($data['scale']);
		$ent->spawnPos = ["x" => $data["posX"], "y" => $data["posY"], "z" => $data["posZ"]];
		$ent->setNameTagAlwaysVisible(true);
		return $ent;
	}

	public function setSkin($entity, $data){
		$skin = $this->getManager()->getSkin($data["skin"]);
		if(is_null($skin)){
			$skin = $data["skin"];
			throw new BossException("SKIN $skin NOT FOUND!", E_WARNING);
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		$entity->setSkin($skin);
		return $entity;
	}

	public function spawnAllBoss(){
		foreach (array_keys($this->getAllBoss()) as $id) {
			$data = $this->getBossConfig()->get($id);
			$nbt = $this->makeNBT($id, $data);
			$ent = $this->makeEntity($id, $data, $nbt);
			$ent->spawnToAll();
		}
		Server::getInstance()->broadcastMessage(TF::BOLD. TF::BLUE. "");
	}

	public function clearAllBoss() {
		foreach(Server::getInstance()->getLevels() as $level){
			foreach($level->getEntities() as $entity){
				if($entity->namedtag->hasTag('BossID', IntTag::Class)){
					$entity->flagForDespawn();
				}
			}
		}
	}

	public function getNextKey(){
		$count = count($this->getBossConfig()->getAll());
		if($count == null) return 0;
		return $count++;
	}

	public function onDisable(){
		$this->clearAllBoss();
	}
}