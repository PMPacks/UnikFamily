<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\island;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;
use BaliGamerz\SkyBlock\Main;
use BaliGamerz\SkyBlock\RemoveFile;
use BaliGamerz\SkyBlock\Utils;


use pocketmine\level\Position;

class IslandManager {

    /** @var Main */
    private $plugin;

    /** @var Island[] */
    private $islands = [];

    private $islandSleep;

    /**
     * IslandManager constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
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
     * Return if a island is online
     *
     * @param $id
     * @return bool
     */
    public function isOnlineIsland($id): bool
    {
        return isset($this->islands[$id]);
    }

    /**
     * Return all online islands
     *
     * @return Island[]
     */
    public function getOnlineIslands(): array
    {
        return $this->islands;
    }

    /**
     * Return an online island
     *
     * @param $id
     * @return Island
     */
    public function getOnlineIsland($id): ?Island
    {
        return isset($this->islands[$id]) ? $this->islands[$id] : null;
    }

    public function getIslandByPlayer(Player $player): ?Island
    {
        $id = $this->getPlayerConfig($player)['island'];
        if(!empty($id)){
            return isset($this->islands[$id]) ? $this->islands[$id] : null;
        }
        return null;
    }


    /**
     * @param string $player
     * @return Island|null
     */
    public function getIslandByName(string $player): ?Island
    {
        $id = $this->plugin->playerDataPath[strtolower($player)];
        if(!empty($id)){
            return isset($this->islands[$id]) ? $this->islands[$id] : null;
        }
        return null;
    }


    /**
     * Return an island by his owner name
     *
     * @param $ownerName
     * @return null|Island
     */
    public function getIslandByOwner($ownerName): ?Island
    {
        foreach($this->islands as $island) {
            if($island->getOwnerName() == $ownerName) {
                return $island;
            }
        }
        return null;
    }

    /**
     * Add a new island
     *
     * @param Config $config
     * @param $ownerName
     * @param $id
     * @param $members
     * @param $locked
     * @param $home
     * @param $generator
     * @param $level
     * @param $size
     * @param $mine
     * @param $position
     * @param $money
     */
    public function addIsland(Config $config, $ownerName, $id, $members, $locked, $home, $generator, $level, $size, $mine, $position, $money) {
        $pos = explode(":", $position);
        $posObject = new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $this->plugin->getServer()->getLevelByName($id));
        $this->islands[$id] = new Island($config, $ownerName, $id, $members, $locked, $home, $generator, $level, $size, $mine, $posObject, $money);
        if(!isset($this->getPlugin()->islandTop[$id])) {
            $this->getPlugin()->islandTop['island'][$id] = ['level' => $level['level'], 'size' => $size, 'mine' => $mine, 'money' => $money['money']];
        }
    }

    /**
     * Create a new island
     *
     * @param Player $owner
     * @param $island_name
     * @param string $generator
     * @param $spawn
     */
    public function createIsland(Player $owner, $island_name, string $generator, $spawn) {
        $name = strtolower($island_name);
        $names = strtolower($owner->getName());
        $moneyArray = ['money' => 1000, 'tier' => 1, 'max-tier' => $this->plugin->config['island']['max-bank-tier'], 'max-money' => 50000];
        $progressData = ['progressSize' => 250, 'progress' => 0, 'level' => 1];
        $config = new Config(Utils::getIslandPath($name), Config::JSON, [
            "owner" => $names,
            "members" => [],
            "locked" => true,
            "level" => $progressData,
            "home" => "",
            "generator" => $generator,
            "size" => 0,
            "mine" => 0,
            "spawn" => $spawn,
            "money" => $moneyArray
        ]);
        $this->addIsland($config, $names, $name, [], true, "", $generator, $progressData, 0, 0, $spawn, $moneyArray);
        $this->getPlugin()->playerDataPath[$names]["island"] = $name;
    }

    /**
     * Set a island offline
     *
     * @param $id
     */
    public function setIslandOffline($id) {
        if(isset($this->islands[$id])) {
            unset($this->islands[$id]);
        }
    }

    /**
     * Check island by player
     *
     * @param Player $player
     */
    public function checkPlayerIsland(Player $player)
    {
        if($this->plugin->hasBossBar()) {
            $this->plugin->getBossBar()->addPlayer($player);
        }
        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($player);
        if (!empty($config["island"])) {
            $path = Utils::getIslandPath($id = $config["island"]);
            if (is_file($path)) {
                if (!isset($this->islands[$id])) {
                    $server = $this->plugin->getServer();
                    $config = new Config($path, Config::JSON);
                    if (!$server->isLevelLoaded($id)) {
                        $server->loadLevel($id);
                    }
                    $this->addIsland($config, $config->get("owner"), $id, $config->get("members"), $config->get("locked"), $config->get("home"), $config->get("generator"), $config->get('level'), $config->get('size'), $config->get('mine'), $config->get('spawn'), $config->get('money'));
                }
            }
        }
    }

    public function removeIsland(Island $island) {
        if(in_array($island, $this->islands)) {
            unset($this->islands[$island->getIdentifier()]);
            unset($this->getPlugin()->islandTop['island'][$island->getIdentifier()]);
        }
        $server = $this->plugin->getServer();
        if($server->isLevelLoaded($island->getIdentifier())) {
            $level = $server->getLevelByName($island->getIdentifier());
            foreach($level->getPlayers() as $player) {
                $player->teleport($server->getDefaultLevel()->getSafeSpawn());
            }
            foreach($level->getEntities() as $entity) {
                $entity->kill();
            }
            $server->unloadLevel($level);
            RemoveFile::deletedFile($island->getIdentifier());
            unlink($this->plugin->getDataFolder()."islands/".$island->getIdentifier().".json");
        }
    }

    /**
     * Try to unload a island if there isn't online players
     *
     * @param Player $player
     */
    public function unloadByPlayer(Player $player) {
        $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($player);
        if(!empty($config["island"])) {
            $id = $config["island"];
            if($this->isOnlineIsland($id)) {
                $island = $this->getOnlineIsland($id);
                $online = false;
                foreach($island->getAllMembers() as $member) {
                    if($member != strtolower($player->getName())) {
                        $user = $this->plugin->getServer()->getPlayerExact($member);
                        if($user instanceof Player and $user->isOnline()) {
                            $online = true;
                            break;
                        }
                    }
                }
                if(!$online) {
                    $level = $this->plugin->getServer()->getLevelByName($id);
                    $island->update();
                    $this->setIslandOffline($id);
                    $this->plugin->getServer()->unloadLevel($level);
                }
            }
        }
    }

    /**
     * Return player config
     *
     * @param Player $player
     * @return mixed
     */
    public function getPlayerConfig(Player $player)
    {
        return $this->plugin->playerDataPath[strtolower($player->getName())];
    }

    public function update() {
        foreach($this->islands as $island) {
            $island->update();
        }
    }

    public function updateDisableIslandServer() {
        foreach($this->islands as $island) {
            $island->update();
            $this->islandSleep++;
            $this->plugin->getLogger()->info(TextFormat::GREEN."Saving island user: ".TextFormat::WHITE."#".$this->islandSleep);
        }
        $this->islandSleep = 0;
    }
}