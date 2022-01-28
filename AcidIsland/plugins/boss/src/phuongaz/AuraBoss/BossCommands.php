<?php

namespace phuongaz\AuraBoss;

use phuongaz\AuraBoss\Boss;
use pocketmine\Server;
use pocketmine\Command\{Command,CommandSender};
use pocketmine\Player;
use jojoe77777\FormAPI\{
	CustomForm,
	SimpleForm
};
use pocketmine\utils\TextFormat as TF;

use phuongaz\AuraBoss\Entity\LvlEntity;
Class BossCommands extends Command{

	private $main;

	public function __construct(Boss $main){
		parent::__construct("boss", "Aura boss system", "/boss");
		$this->main = $main;
	}

	public function execute(CommandSender $sender, string $label, array $args):bool {
		if($sender instanceof Player){
			if(!$sender->isOp()) return true;
			$this->openForm($sender);	
		} 
		if(isset($args[0]) and $args[0] == 'clear-all'){
			$sender->sendMessage("All boss has been removed");
			$this->main->clearAllBoss();
		}
		if(isset($args[0]) and $args[0] == 'spawn-all'){
			$this->main->spawnAllBoss();
		}
		if(isset($args[0]) and $args[0] == 'cooldown' and isset($args[1]) and isset($args[2])){
			$this->main->getManager()->spawnCountDown($args[1], $args[2]);
		}else {
			$sender->sendMessage("cooldown id time");
/*			$key = $this->main->getNextKey();
			echo $key;
			var_dump($key);*/
		}

		return true;
	}

	public function openForm(CommandSender $sender){
		$form = new SimpleForm(function(Player $player, $data){
			if(is_null($data)) return;
			if($data == 0) $this->openSpawnForm($player);
			if($data == 1) $this->openSettingForm($player);
		});
		$form->setTitle(TF::BOLD. TF::GREEN. "AURA BOSS SYSTEM");
		$form->addButton(TF::BLUE. TF::BOLD. "Spawn boss");
		$form->addButton(TF::BLUE. TF::BOLD."CreateBoss");
		$form->sendToPlayer($sender);
	}

	public function openSpawnForm(Player $player) {
		$form = new SimpleForm(function(Player $player, $data){
			if(is_null($data)) return;
			$player->sendMessage(TF::BOLD. TF::GREEN . "Boss has been spawned");
			$this->main->spawnBoss($data);
			//var_dump($this->main->getBossData($data));
		});

		$form->setTitle(TF::RED. TF::BOLD. "SPAWN BOSS");
		foreach(array_keys($this->main->getAllBoss()) as $id){
		//	var_dump($this->main->getAllBoss($id)); FOR DEBUG
			$name = $this->main->config->get($id)["name"];

		//	var_dump($this->main->getBossData($id)); FOR DEBUG
			$form->addButton(TF::DARK_BLUE. TF::BOLD. "SPAWN \n ".TF::RESET. $name);
		}
		$form->sendToPlayer($player);
	}


	public function openSettingForm($player){
			$form = new CustomForm(function(Player $player, $data){
			if(is_null($data)) return;
			$key = $this->main->getNextKey();
			$databoss = [
				"name" => $data[1],
				"skin" => $data[0],
				"maxhealth" =>(int) $data[2],
				"damage" => (int)$data[3],
				"speed" => (int) $data[4],
				"posX" => $player->getX(),
				"posY" => $player->getY(),
				"posZ" => $player->getZ(),
				"level" => $player->getLevel()->getName(),
				"drops" => ["1:0:1", "4:0:64"],
				"scale" => (int)$data[5],
				"commands" => ["boss cooldown $key 20"],
				"custom-item" => [1 => ["id" => "278:0:1", "chance" => 50, "name" => "§l§aSuper Pickaxe", "lore" => "§l§aRARITY:§e **", "enchantment" => ["17:5", "15:8"]]]
			];
			$this->main->getBossConfig()->save();
			$this->main->SaveBoss($key, $databoss);
		});

		$form->setTitle(TF::RED. TF::BOLD."§6Setting mobs");
		$form->addInput(TF::BLUE. TF::BOLD."Type skin", "name skin and geometry");
		$form->addInput(TF::BLUE. TF::BOLD."name", "aura boss", $player->getName(). " boss");
		$form->addInput(TF::BLUE. TF::BOLD."Health", "100", "100");
		$form->addInput(TF::BLUE. TF::BOLD."Damage", "1.5", "1.5");
		$form->addInput(TF::BLUE. TF::BOLD."Speed", "1.2", "1.2");
		$form->addInput(TF::BLUE. TF::BOLD."Scale", "1.2", "1.5");
		$form->sendToPlayer($player);	
	}
}