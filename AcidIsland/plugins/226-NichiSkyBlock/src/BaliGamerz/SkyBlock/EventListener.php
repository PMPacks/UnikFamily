<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock;


use BaliGamerz\SkyBlock\Entity\NpcClass;
use BaliGamerz\SkyBlock\Entity\SkyBlockTNT;
use BaliGamerz\SkyBlock\events\PlayerSuccessBuyEvent;
use BaliGamerz\SkyBlock\Menu\PublicMenu;
use BaliGamerz\SkyBlock\Task\CompressionAsyncTask;
use MadeAja\NichiPortals\EnterPortalEvent;
use pocketmine\block\Block;
use pocketmine\block\TNT;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\Listener;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector2;

use pocketmine\Player;

use pocketmine\Server;
use pocketmine\utils\TextFormat;

use BaliGamerz\SkyBlock\island\Island;


class EventListener implements Listener
{

    /** @var Main */
    public $plugin;

    /**
     * EventListener constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return Main
     */
    public function getPlugin(): Main
    {
        return $this->plugin;
    }

    /**
     * Try to register a player
     *
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event)
    {
        $this->plugin->getSkyBlockManager()->tryRegisterUser($event->getPlayer());
    }

    /**
     * Executes onJoin actions
     *
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event)
    {
        $this->plugin->getIslandManager()->checkPlayerIsland($event->getPlayer());
    }


//    /**
//     * @param EnterPortalEvent $event
//     */
//    public function enterPortal(EnterPortalEvent $event)
//    {
//        $player = $event->getPlayer();
//        $menu = new PublicMenu();
//        if ($event->getPortalId() === 192) {
//            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($player);
//            if (empty($config["island"])) {
//                $menu->onMenu($player);
//                return;
//            }
//            $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
//            if ($island instanceof Island) {
//                $home = $island->getHomePosition();
//                if ($home !== null) {
//                    $player->teleport($home);
//                    return;
//                }
//                $server = $this->plugin->getServer();
//                if (!$server->isLevelLoaded($island->getIdentifier())) {
//                    $server->loadLevel($island->getIdentifier());
//                }
//                $player->teleport(new Position($island->getPosition()->x, $island->getPosition()->y + 2, $island->getPosition()->z, $this->getPlugin()->getServer()->getLevelByName($island->getIdentifier())));
//                return;
//            }
//        }
//    }

    /**
     * Executes onLeave actions
     *
     * @param PlayerQuitEvent $event
     */
    public function onLeave(PlayerQuitEvent $event)
    {
        $this->plugin->getIslandManager()->unloadByPlayer($event->getPlayer());
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event)
    {
        $sender = $event->getPlayer();
        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if ($island instanceof Island) {
            if (!$event->getPlayer()->isOp() and !in_array(strtolower($event->getPlayer()->getName()), $island->getAllMembers())) {
                $event->getPlayer()->sendPopup(TextFormat::RED . "Bạn phải là một thành viên của hòn đảo này để phá vỡ ở đây!");
                $event->setCancelled();
            }
            $island->addMine(1);
        }
        $this->plugin->runningQuest($sender, $event->getBlock(), 'break');
    }


    /**
     * @param PlayerSuccessBuyEvent $event
     */
    public function playerSuccessBuyShop(PlayerSuccessBuyEvent $event)
    {
        $player = $event->getPlayer();
        $data = $event->getItem();
        $item = Item::get($data['id'], $data['meta'], $data['count']);
        $this->plugin->runningQuest($player, $item, 'buy');
    }

    /**
     * @param CraftItemEvent $event
     */
    public function craftItemQuest(CraftItemEvent $event)
    {
        $array = $event->getOutputs();
        $item = array_pop($array);
        $player = $event->getPlayer();
        $this->plugin->runningQuest($player, $item, 'craft');
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if ($block instanceof TNT) {
            $event->setCancelled();
            $nbt = Entity::createBaseNBT($block->asVector3()->add(0.5, 0, 0.5), new Vector3(-sin(2.2431915802349) * 0.02, 0.2, -cos(2.2431915802349) * 0.02));
            $entity = new SkyBlockTNT($block->level, $nbt);
            $entity->setOwningEntity($player);
            $entity->mircotime = microtime(true) + 4.1;
            $entity->setNameTagAlwaysVisible();
            $entity->setNameTagVisible();
            $entity->spawnToAll();
        }
        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if ($island instanceof Island) {
            if (!$event->getPlayer()->isOp() and !in_array(strtolower($event->getPlayer()->getName()), $island->getAllMembers())) {
                if (!$island->isLocked()) return;
                $event->getPlayer()->sendPopup(TextFormat::RED . "Bạn phải là một thành viêm của hòn đảo này để đặt ở đây!");
                $event->setCancelled();
            } else {
                $island->setSizePlus(1);
                $block = $event->getBlock();
                $a = $block->getId() . ":" . $block->getDamage();
                if (array_key_exists($a, $this->getPlugin()->config['islandLevelBlocks'])) {
                    $island->addprogress($this->getPlugin()->config['islandLevelBlocks'][$block->getId() . ":" . $block->getDamage()]);
                }
                $this->plugin->runningQuest($event->getPlayer(), $event->getBlock(), 'place');
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event)
    {

        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if ($island instanceof Island) {
            if (!$event->getPlayer()->isOp() and !in_array(strtolower($event->getPlayer()->getName()), $island->getAllMembers())) {
                $event->getPlayer()->sendPopup(TextFormat::RED . "Bạn phải là một thành viên của hòn đảo này để đặt ở đây!");
                $event->setCancelled();
            }
        }
    }

    /**
     * Tries to remove a player on change event
     *
     * @param EntityLevelChangeEvent $event
     */
    public function onLevelChange(EntityLevelChangeEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            if ($this->plugin->getIslandManager()->isOnlineIsland($event->getOrigin()->getName())) {
                $this->plugin->getIslandManager()->getOnlineIsland($event->getOrigin()->getName())->tryRemovePlayer($entity);
            } else if ($this->plugin->getIslandManager()->isOnlineIsland($event->getTarget()->getName())) {
                if ($this->plugin->getIslandManager()->getOnlineIsland($event->getTarget()->getName())->addPlayer($entity)) {
                    $this->plugin->addPlayerQuest($entity);
                }
            }
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */

    public function playerDeathEvent(PlayerDeathEvent $event)
    {
        if (Main::getInstance()->config['keep-inventory'] === true) {
            $event->setKeepInventory(true);
        }
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @ignoreCancelled true
     *
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($event instanceof EntityDamageByEntityEvent) {
            $damage = $event->getDamager();
            if ($damage instanceof Player) {
                if ($entity instanceof NpcClass) {
                    $event->setCancelled(true);
                    if ($entity->namedtag->hasTag("damageEntity")) {
                        (new PublicMenu())->playerFormClicked($damage);
                        return;
                    }
                    if ($entity->namedtag->hasTag("npcStats")) {
                        (new PublicMenu())->islandStats($damage);
                        return;
                    }
                    switch ($entity->getName()) {
                        case "{mine_top}":
                            if (!$damage->isSneaking()) {
                                if ($this->plugin->addIndexUserIslandTop($damage, 'mine')) {
                                    $entity->sendNameTag($damage);
                                }
                                return;
                            }
                            if ($this->plugin->reduceIndexUserIslandTop($damage, 'mine')) {
                                $entity->sendNameTag($damage);
                            }
                            break;
                        case "{level_top}":
                            if (!$damage->isSneaking()) {
                                if ($this->plugin->addIndexUserIslandTop($damage, 'level')) {
                                    $entity->sendNameTag($damage);
                                }
                                return;
                            }
                            if ($this->plugin->reduceIndexUserIslandTop($damage, 'level')) {
                                $entity->sendNameTag($damage);
                            }
                            break;
                        case "{size_top}":
                            if (!$damage->isSneaking()) {
                                if ($this->plugin->addIndexUserIslandTop($damage, 'size')) {
                                    $entity->sendNameTag($damage);
                                }
                                return;
                            }
                            if ($this->plugin->reduceIndexUserIslandTop($damage, 'size')) {
                                $entity->sendNameTag($damage);
                            }
                            break;
                        case "{money_top}":
                            if (!$damage->isSneaking()) {
                                if ($this->plugin->addIndexUserIslandTop($damage, 'money')) {
                                    $entity->sendNameTag($damage);
                                }
                                return;
                            }
                            if ($this->plugin->reduceIndexUserIslandTop($damage, 'money')) {
                                $entity->sendNameTag($damage);
                            }
                            break;
                    }
                }
                $island = $this->plugin->getIslandManager()->getOnlineIsland($damage->getLevel()->getName());
                if ($island instanceof Island) {
                    if (!$damage->isOp() and !in_array(strtolower($damage->getName()), $island->getAllMembers())) {
                        $damage->sendPopup(TextFormat::RED . "Bạn phải là một thành viên của hòn đảo này để thiệt hại ở đây!");
                        $event->setCancelled();
                    }
                }
            }
        }
    }

    /**
     * @param LevelUnloadEvent $event
     */
    public function onUnloadLevel(LevelUnloadEvent $event)
    {
        foreach ($event->getLevel()->getPlayers() as $player) {
            $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }

    public function playerMoveEvent(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $island = $this->plugin->getIslandManager()->getOnlineIsland($event->getPlayer()->getLevel()->getName());
        if ($island instanceof Island) {
            $level = $this->getPlugin()->getServer()->getLevelByName($island->getIdentifier());
            $distance = $level->getSpawnLocation()->distance($player);
            if ($distance >= $this->getPlugin()->config['island']['island_border']) {
                $event->setCancelled(true);
                $player->sendMessage($this->getPlugin()->config['island']["border_message"]);
            }
        }
        $from = $event->getFrom();
        $to = $event->getTo();
        if ($from->distance($to) < 0.1) {
            return;
        }
        foreach ($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(10, 10, 10), $player) as $e) {
            if ($e instanceof Player) {
                continue;
            }
            $xdiff = $player->x - $e->x;
            $zdiff = $player->z - $e->z;
            $angle = atan2($zdiff, $xdiff);
            $yaw = (($angle * 180) / M_PI) - 90;
            $ydiff = $player->y - $e->y;
            $v = new Vector2($e->x, $e->z);
            $dist = $v->distance($player->x, $player->z);
            $angle = atan2($dist, $ydiff);
            $pitch = (($angle * 180) / M_PI) - 90;
            if ($e->getSaveId() === "NpcClass") {
                $pk = new MovePlayerPacket();
                $pk->entityRuntimeId = $e->getId();
                $pk->position = $e->asVector3()->add(0, $e->getEyeHeight(), 0);
                $pk->yaw = $yaw;
                $pk->pitch = $pitch;
                $pk->headYaw = $yaw;
                $pk->onGround = $e->onGround;
                $player->dataPacket($pk);
            }
        }
    }


    /**
     * @param EntityMotionEvent $event
     *
     * @return void
     */
    public function onEntityMotion(EntityMotionEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof NpcClass) {
            $event->setCancelled();
        }
    }
}

