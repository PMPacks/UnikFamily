<?php

declare(strict_types=1);

namespace minefore\mf;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use SellHand\Main as SellHand;

class Eventss implements Listener{
    public function onBreak(BlockBreakEvent $e){
		if($e->isCancelled()){
	    return false;
		}else{
    	$o = $e->getPlayer();
    	$isim = $o->getName();
        $l = $o->getLevel();
        $b = $e->getBlock();
        $api = Main::getAPI();
         if($api->cfg->get(strtolower($isim)) == "on"){
			 if($o->getInventory()->getItemInHand()->getId() == 278){
				if($o->getInventory()->getItemInHand()->hasEnchantment(18)){
					$sl = $o->getInventory()->getItemInHand()->getEnchantment(18)->getLevel();
					$sl = $sl > 1000 ? 1000 : $sl;
				}
			 }
			if($b->getId() == 14){
        		$e->setCancelled(false);
				$it = Item::get(266,0,isset($sl) ? mt_rand(1,$sl) : 1);
				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it);				
				}
				return true;
        	}
            if($b->getId() == 15){
        		$e->setCancelled(false);
				$it = Item::get(265,0,isset($sl) ? mt_rand(1,$sl) : 1);
				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it);				
				}
				return true;
        	}
            if($b->getId() == 16){
                $e->setCancelled(true);
				$it = Item::get(263,0,isset($sl) ? mt_rand(1,$sl) : 1);
				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it);					
				}
				return true;
            }
            if($b->getId() == 56){
                $e->setCancelled(true);
				$it = Item::get(264,0,isset($sl) ? mt_rand(1,$sl) : 1);
				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it);				
				}
				return true;
            }
        	if($b->getId() == 129){
        		$e->setCancelled(true);
				$it = Item::get(388,0,isset($sl) ? mt_rand(1,$sl) : 1);
 				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it); 				
				}
				return true;
        	}
        	if($b->getId() == 21){
        		$e->setCancelled(true);
				$it = Item::get(351,4,isset($sl) ? mt_rand(1,$sl) : 1);
				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it); 					
				}
				return true;
        	}
        	if($b->getId() == 73){
        		$e->setCancelled(true);
				$it = Item::get(331,0,isset($sl) ? mt_rand(1,$sl) : 1);
				if($o->getInventory()->canAddItem($it)){
					$o->getInventory()->addItem($it);					
				}
				return true;
        	}
        }else{
        	return true;
		}
		}
	}
}