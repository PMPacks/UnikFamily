<?php

declare(strict_types=1);

namespace CLADevs\Minion;

use CLADevs\Minion\minion\Minion;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\{StringTag, CompoundTag};
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use pocketmine\Player;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\level\Position;

class EventListener implements Listener{


    
    public function onInteract(PlayerInteractEvent $e): void{
        $player = $e->getPlayer();
        $name = Main::get()->checkName($player);
        $item = $e->getItem();
        $dnbt = $item->getNamedTag();
        if(!$e->isCancelled()){
            if($dnbt->hasTag("summon", StringTag::class)){
                if(($dnbt->getTag("player", StringTag::class)->getValue() == $name) || $player->isOp()){
                    $pos = new Position($player->x, $player->y+0.5, $player->z, $player->level);
                    $nbt = Entity::createBaseNBT($pos, null, $player->getYaw(), $player->getPitch());
                    $nbt->setString("player", $dnbt->getTag("player", StringTag::class)->getValue());
                    if(Main::get()->hasPer(Main::SELL, $player)){ $nbt->setString("SELL", "yes");} else $nbt->setString("SELL", "no");
                    if(Main::get()->hasPer(Main::DOUBLE_CHEST, $player)) $nbt->setString("DOUBLE_CHEST", "yes");
                    if(Main::get()->hasPer(Main::SELL, $player)){ $nbt->setString("ORE", "yes");} else $nbt->setString("ORE", "no");
                    $nbt->setTag(new CompoundTag("Skin", [
                    "Data" => new StringTag("Data", Main::get()->getSkin()->getSkinData()),
                    "Name" => new StringTag("Name", "Minion")]));
                    $skinTag = $player->namedtag->getCompoundTag("Skin");
                    assert($skinTag !== null);
                    $entity = new Minion($player->getLevel(), $nbt);            
                    $entity->spawnToAll();
                    $entity->setOwner($player);
                    $item->setCount($item->getCount() - 1);
                    $player->getInventory()->setItemInHand($item);
                }else $player->sendMessage("§l§e•>§c Minion này không thuộc quyền sở hửu của bạn, hãy trả lại nó");            
            }            
        }

    }

    public function onDeath(EntityDeathEvent $event) :void {
        $entity = $event->getEntity();
        if($entity instanceof Minion){
            $event->setCancelled();
            $owner = $entity->getOwnerByName();
            $inv = $owner->getInventory();
            $item = Main::get()->getItem($owner);
            if($inv->canAddItem($item)){
                $inv->addItem($item);
            }
        }
    }

    public function onEntitySpawn(EntitySpawnEvent $e): void{
        $entity = $e->getEntity();
        if($entity instanceof Minion){
            $pl = Server::getInstance()->getPluginManager()->getPlugin("ClearLagg");
            if($pl !== null) $pl->exemptEntity($entity);
        }
    }

    public function onEntityMotion(EntityMotionEvent $event): void {
        $entity = $event->getEntity();
        if ($entity instanceof Minion) {
            $event->setCancelled();
        }
    }
}
