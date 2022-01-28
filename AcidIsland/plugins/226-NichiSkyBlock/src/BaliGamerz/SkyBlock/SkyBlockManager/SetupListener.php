<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\SkyBlockManager;


use BaliGamerz\SkyBlock\Main;
use BaliGamerz\SkyBlock\Task\CompressionAsyncTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\HandlerList;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

class SetupListener implements Listener
{

    private $plugin;
    private $owner;
    private $setup;
    
    
    public function __construct(Main $plugin, Player $owner, array $setup)
    {
        $this->plugin = $plugin;
        $this->owner = $owner;
        $this->setup = $setup;
    }

    public function onBreak(BlockBreakEvent $event){
        $sender = $event->getPlayer();
        if($this->owner->getName() !== $sender->getName())return;
            $event->setCancelled();
            $index = $this->setup['index'];
            $pos = $event->getBlock()->asVector3();
            if ($index === 1) {
                $this->setup["spawn"] = floor($pos->getX()) . ":" . floor($pos->getY() + 1) . ":" . floor($pos->getZ());
                Main::sendMessage($sender, "Hãy phá khối để đặt npc đảo");
                $this->setup['index']++;
            }
            if ($index === 2) {
                $pos = $event->getBlock()->asVector3();
                $this->setup["npcPosition"] = floor($pos->getX()) . ":" . floor($pos->getY() + 1) . ":" . floor($pos->getZ());
                Main::sendMessage($sender, "Vui lòng phá vỡ khối để đặt vị trí biển báo đảo");
                $this->setup['index']++;
            }
            if ($index === 3) {
                $pos = $event->getBlock()->asVector3();
                $this->setup["signPosition"] = floor($pos->getX()) . ":" . floor($pos->getY()) . ":" . floor($pos->getZ());
                $data = $this->setup;
                $this->plugin->dataThemas['list'][$data['name']] = [
                    "name" => $data['name'],
                    "dirname" => $data['dirname'],
                    "spawn" => $data['spawn'],
                    "button-type" => $data['button-type'],
                    "button-img" => $data['button-img'],
                    "npcPosition" => $data['npcPosition'],
                    "signPosition" => $data['signPosition'],
                    "item" => $data['item'],
                    "payment" => $data['payment']
                ];
                $task = new CompressionAsyncTask([
                    Server::getInstance()->getDataPath() . "worlds/" . $data['dirname'],
                    $this->plugin->getDataFolder() . $data['dirname'] . ".zip", true], $sender, $data, microtime(true));
                $this->plugin->getServer()->getAsyncPool()->submitTask($task);
                $this->setup['index'] = 10;
                HandlerList::unregisterAll($this);
            }
    }

    public function playerQuit(PlayerQuitEvent $event){
        $sender = $event->getPlayer();
        if($this->owner->getName() !== $sender->getName())return;
        HandlerList::unregisterAll($this);
    }
}