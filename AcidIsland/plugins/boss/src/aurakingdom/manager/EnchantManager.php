<?php

namespace aurakingdom\manager;

use pocketmine\item\Item;

use pocketmine\enchant\Enchantment;
use pocketmine\enchant\EnchantmentInstance;

Class EnchantManager {

	/**
	* @param Item $item
	* @param array[] $enchantments
	* @return Item
	*/
	public static function addEnchantemts(Item $item, array $enchants) :Item {
		foreach($enchants as $enchantment => $level) {
			if(is_string($enchantment)){
				$ce = Enchantment::getEnchantmentByName($enchantment);
			}
			if(is_numeric($enchantment)){
				$ce = Enchantment::getEnchantment($enchantment);
			}
			if(!is_null($ce)){
				$item->addEnchantment(new EnchantmentInstance($ce, $level));
			}			
		}
		return $item;
	}

	/**
	* @param Item $item
	* @param int|string $idce
	* @param int $level
	* @return Item
	*/
	public static function addEnchantment(Item $item, $idce, int $level) :Item {
		if(is_string($idce)){
			$ce = Enchantment::getEnchantmentByName($idce);
		}
		if(is_numeric($idce)){
			$ce = Enchantment::getEnchantment($idce);
		}
		if(!is_null($ce)){
			$item->addEnchantment(new EnchantmentInstance($ce, $level));
		}
		return $item;			
	}
}
