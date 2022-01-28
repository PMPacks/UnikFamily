<?php

namespace SonsaYT\Crossbow;

use SonsaYT\Crossbow\item\Crossbow;

use pocketmine\plugin\PluginBase;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class Main extends PluginBase {

    public function onEnable() {
		ItemFactory::registerItem(new Crossbow(), true);
		Item::initCreativeItems();
    }
	
}
