<?php

namespace KOB;

use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\block\block;
use pocketmine\event\block\BlockBreakEvent;
class EventListener implements Listener{
    public function onBreak(BlockBreakEvent $e){
        if($e->isCancelled()){
            return false;
        } else{
                $o = $e->getPlayer();
        $isim = $o->getName();
        $l = $o->getLevel();
        $b = $e->getBlock();
        $api = Main::getAPI();
         if($api->config->get(strtolower($isim)) == "on"){
             if($o->getInventory()->getItemInHand()->getId() == 278){
                if($o->getInventory()->getItemInHand()->hasEnchantment(18)){
                    $sl = $o->getInventory()->getItemInHand()->getEnchantment(18)->getLevel();
                    $sl = $sl > 1000 ? 1000 : $sl;
                }
             }
            if($b->getId() == 14){
                $e->setDrops([]);
                $it = Item::get(41,0,isset($sl) ? mt_rand(1,$sl) : 1);
                if($o->getInventory()->canAddItem($it)){
                    $o->getInventory()->addItem($it);                  
                }
                return true;
            }
            if($b->getId() == 15){
                $e->setDrops([]);
                $it = Item::get(42,0,isset($sl) ? mt_rand(1,$sl) : 1);
                if($o->getInventory()->canAddItem($it)){
                    $o->getInventory()->addItem($it);                
                }
                return true;
            }
            if($b->getId() == 16){
                $e->setDrops([]);
                $it = Item::get(16,0,isset($sl) ? mt_rand(1,$sl) : 1);
                if($o->getInventory()->canAddItem($it)){
                    $o->getInventory()->addItem($it);                   
                }
                return true;
            }
            if($b->getId() == 56){
               $e->setDrops([]);
                $it = Item::get(57,0,isset($sl) ? mt_rand(1,$sl) : 1);
                if($o->getInventory()->canAddItem($it)){
                    $o->getInventory()->addItem($it);               
                }
                return true;
            }
            if($b->getId() == 129){
                $e->setDrops([]);
                $it = Item::get(388,0,isset($sl) ? mt_rand(1,$sl) : 1);
                if($o->getInventory()->canAddItem($it)){
                    $o->getInventory()->addItem($it);               
                }
                return true;
            }
            if($b->getId() == 21){
                $e->setDrops([]);
                $it = Item::get(22,4,isset($sl) ? mt_rand(1,$sl) : 1);
                if($o->getInventory()->canAddItem($it)){
                    $o->getInventory()->addItem($it);                   
                }
                return true;
            }
            if($b->getId() == 73){
                $e->setDrops([]);
                $it = Item::get(152,0,isset($sl) ? mt_rand(1,$sl) : 1);
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