<?php

namespace phuongaz\OreGenerator;

use pocketmine\Player;
use pocketmine\tile\Spawnable;
use pocketmine\block\Block;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\level\sound\FizzSound;
use pocketmine\item\Item;
Class GeneratorTile extends Spawnable{

	private $ores = [
		15,
		16,
		14,
		129,
		73,
		56,
		21,
		4,
	];

	private $levelgen = 1;

	public function onUpdate() :bool{
		if($this->getBlock()->getId() !== Block::STONECUTTER){
			$this->close();
			return false;
		}
		$block = $this->getBlock();
		$pos = $this->getBlock()->asPosition()->add(0, 1);
		$upBlock = $this->getBlock()->getLevel()->getBlock($pos);
				$newBlock = $this->getNewBlock();
		$t = $block->getLevel()->getTile(new \pocketmine\math\Vector3($block->x, $block->y + 1, $block->z));
		if($upBlock->getLevel()->getServer()->getTick() % (22 - $this->levelgen) == 0){
		if($t instanceof \pocketmine\tile\Chest){
			$t->getInventory()->addItem(Item::get($newBlock->getId(), 0, 1));
			$this->getBlock()->getLevel()->addSound(new FizzSound($this->getBlock()->asVector3()));
		}
	}
		if($upBlock->getId() == Block::AIR){
			if($upBlock->getLevel()->getServer()->getTick() % (22 - $this->levelgen) == 0){
				if(!$t instanceof \pocketmine\tile\Chest){
				$newBlock = $this->getNewBlock();
				$this->getBlock()->getLevel()->setBlock($upBlock, $newBlock, true, true);
			}
			}
		}
		return true;
	}

	public function getNewBlock() :Block{
		$level = $this->levelgen;
		$chance = mt_rand(3, (14 - $level)+3);
		if($chance == 3){
			return Block::get($this->ores[array_rand($this->ores)]);
		}
		return Block::get(4);
	}

	public function getLevelGenerator(){
		return $this->levelgen;
	}

    protected function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setString("id", "StoneCutter");
    }

    protected function readSaveData(CompoundTag $nbt): void {
    	$this->scheduleUpdate();
        $this->levelgen = $nbt->getInt("generator");
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt("generator", $this->levelgen);
    }
}