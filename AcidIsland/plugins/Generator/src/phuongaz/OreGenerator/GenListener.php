<?php

namespace phuongaz\OreGenerator;

use pocketmine\event\block\{BlockPlaceEvent, BlockBreakEvent};
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;

Class GenListener implements Listener{

	public function BlockPlace(BlockPlaceEvent $event){
		$block = $event->getBlock();
		$item = $event->getItem();
		if($block->getId() == Block::STONECUTTER){
            $nbt = GeneratorTile::createNBT($block);
            $nbt_item = $item->getNamedTag();
            if($nbt_item->hasTag("generator", IntTag::class)){
            	$levelgen = $nbt_item->getInt("generator");
            }else $levelgen = 1;
            $nbt->setInt("generator", $levelgen);
            $Tile = new GeneratorTile($block->getLevel(), $nbt);
            $Tile->spawnToAll();
		}
	}

	public function BlockBreakEvent(BlockBreakEvent $event){
		$block = $event->getBlock();
		if($block->getId() == Block::STONECUTTER){
			$tile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if($tile instanceof GeneratorTile){
				$tile_level = $tile->getLevelGenerator();
				$item = Item::get(245);
				$item->setCustomName("§l§fOregen §e(§6Level:§c ".$tile_level."§e)");
				$nbt = $item->getNamedTag();
				$nbt->setInt("generator", $tile_level);
				$item->setNamedTag($nbt);
				$event->setDrops([$item]);
			}
		}
	}
}