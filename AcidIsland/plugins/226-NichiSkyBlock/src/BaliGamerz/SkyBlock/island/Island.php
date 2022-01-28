<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝



namespace BaliGamerz\SkyBlock\island;


use BaliGamerz\SkyBlock\Main;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\Config;
use BaliGamerz\SkyBlock\Utils;
use pocketmine\entity\Skin;

class Island
{

    /** @var Config */
    private $config;

    /** @var string */
    private $ownerName;

    /** @var string */
    private $identifier;

    /** @var Player[] */
    private $playersOnline = [];

    /** @var string[] */
    private $members;

    /** @var bool */
    private $locked;

    /**
     * @var array $money
     */
    public $money;

    /** @var string */
    private $home;

    /**
     * @var array $level
     */
    public $level;

    /** @var int */
    private $size;

    /** @var string */
    private $generator;

    private $position;

    private $mine;


    /**
     * Island constructor.
     *
     * @param Config $config
     * @param string $ownerName
     * @param string $identifier
     * @param array $members
     * @param bool $locked
     * @param string $home
     * @param string $generator
     * @param array $level
     * @param int $size
     * @param $mine
     * @param $position
     * @param array $money
     */
    public function __construct(Config $config, $ownerName, $identifier, $members, $locked, $home, $generator, $level, $size, $mine, $position, $money)
    {
        $this->config = $config;
        $this->ownerName = $ownerName;
        $this->identifier = $identifier;
        $this->members = $members;
        $this->locked = $locked;
        $this->home = $home;
        $this->level = $level;
        $this->size = $size;
        $this->generator = $generator;
        $this->position = $position;
        $this->money = $money;
        $this->mine = $mine;

    }

    /**
     * Return island config
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Return owner name
     *
     * @return string
     */
    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    /**
     * Return identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }


    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * Return level
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level['level'];
    }

    /**
     * @param int $size
     */
    public function setSizePlus(int $size): void
    {
        $this->size = $this->size + $size;
    }

    /**
     * @param int $size
     */
    public function setSizeMinus(int $size): void
    {
        $this->size = $this->size - $size;
    }

    /**
     * Return size
     *
     * @return int
     */
    public function getSize(): int
    {
        return isset($this->size) ? $this->size : 0;
    }

    /**
     * Return island online players
     *
     * @return Player[]
     */
    public function getPlayersOnline(): array
    {
        return $this->playersOnline;
    }

    /**
     * Return island members
     *
     * @return string[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * Return if the island is locked
     *
     * @return boolean
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * Return island home not parsed
     *
     * @return string
     */
    public function getHome(): string
    {
        return $this->home;
    }

    /**
     * Return home position
     *
     * @return null|Position
     */
    public function getHomePosition(): ?Position
    {
        return Utils::parsePosition($this->home);
    }

    /**
     * Return if the island has a home
     *
     * @return bool
     */
    public function hasHome(): bool
    {
        return $this->getHomePosition() instanceof Position;
    }

    /**
     * Return all members (also the owner)
     *
     * @return array|\string[]
     */
    public function getAllMembers(): array
    {
        $members = $this->members;
        $members[] = $this->ownerName;
        return $members;
    }


    /**
     * Return island generator
     *
     * @return string
     */
    public function getGenerator(): string
    {
        return $this->generator;
    }

    /**
     * Add a player to the island
     *
     * @param Player $player
     * @return bool
     */
    public function addPlayer(Player $player): bool
    {
        if (in_array(strtolower($player->getName()), $this->getAllMembers())) {
            $this->playersOnline[] = $player->getName();
            return true;
        }
        return false;
    }

    /**
     * Set owner name
     *
     * @param $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * Set island identifier
     *
     * @param string $identifier
     */
    public function setIdentifier(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Set island players online
     *
     * @param Player[] $playersOnline
     */
    public function setPlayersOnline($playersOnline)
    {
        $this->playersOnline = $playersOnline;
    }


    /**
     * @param int $level
     */
    public function addProgress(int $level): void
    {
        $this->level['progress'] += $level;
        if ($this->level['progress'] > $this->level['progressSize']) {
            $this->level['level']++;
            $this->level['progress'] = 0;
            $this->level['progressSize'] += 250;
        }
    }

    public function getProgress()
    {
        $dataLevel = $this->level;
        return $dataLevel['progress'] / $dataLevel['progressSize'] * 100;
    }

    public function addMine(int $int)
    {
        $this->mine += $int;
    }

    public function getMine()
    {
        return $this->mine;
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function setMoney(int $amount): bool
    {
        $amount = round($amount, 2);
        if ($amount > $this->money['max-money']) {
            return false;
        }
        $this->money['money'] = $amount;
        return false;
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function addMoney(int $amount): bool
    {
        $dataMoney = $this->money['money'];
        $amount = round($amount, 2);
        if((int)$dataMoney + $amount > $this->money['max-money']){
            return false;
        }
        $this->money['money'] += $amount;
        return true;
    }

    /**
     * @return int
     */
    public function getShareMoney(): int
    {
        $dataMoney = $this->money['money'];
        return (int)$dataMoney / count($this->getAllMembers());
    }

    /**
     * @return float|int
     */
    public function getPriceTier()
    {
        return ($this->money['max-money'] * $this->money['tier']);
    }

    /**
     * @return bool
     */
    public function updateMoneyTier(): bool
    {
        if ($this->money['tier'] >= $this->money['max-tier']) {
            return false;
        }
        $this->money['tier']++;
        $this->money['max-money'] = $this->money['max-money'] * $this->money['tier'];
        return true;
    }


    /**
     * @return int
     */
    public function myMoney(): int
    {
        return $this->money['money'];
    }

    /**
     * @param int $amount
     * @return bool
     */
    public function reduceMoney(int $amount): bool
    {
        $dataMoney = (int)$this->money['money'];
        $amount = round($amount, 2);
        if($dataMoney - $amount < 0){
            return false;
        }
        $this->money['money'] -= $amount;
        return true;
    }

    /**
     * Set island members
     *
     * @param string[] $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }

    /**
     * Add a member to the team
     *
     * @param Player $player
     */
    public function addMember(Player $player)
    {
        $this->members[] = strtolower($player->getName());
    }

    /**
     * Set the island locked
     *
     * @param boolean $locked
     */
    public function setLocked($locked = true)
    {
        $this->locked = $locked;
    }

    /**
     * Set not parsed home
     *
     * @param string $home
     */
    public function setHome($home)
    {
        $this->home = $home;
    }

    /**
     * Set home position
     *
     * @param Position $position
     */
    public function setHomePosition(Position $position)
    {
        $this->home = Utils::createPositionString($position);
    }

    /**
     * Set island config
     *
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Set island generator
     *
     * @param string $generator
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    /**
     * Tries to remove a player
     *
     * @param Player $player
     */
    public function tryRemovePlayer(Player $player)
    {
        if (in_array($player->getName(), $this->playersOnline)) {
            unset($this->playersOnline[$player->getName()]);
            unset($this->playersOnline[array_search($player->getName(), $this->playersOnline)]);
        }

    }

    /**
     * Remove member
     *
     * @param string $string
     */
    public function removeMember($string)
    {
        if (in_array($string, $this->members)) {
                unset($this->members[array_search($string, $this->members)]);
        }
    }

    public function update()
    {
        $this->config->set("owner", $this->getOwnerName());
        $this->config->set("home", $this->getHome());
        $this->config->set("locked", $this->isLocked());
        $this->config->set("members", $this->getMembers());
        $this->config->set("level", $this->level);
        $this->config->set("mine", $this->mine);
        $this->config->set("money", $this->money);
        $this->config->save();
        if (isset(Main::getInstance()->islandTop['island'][$this->getIdentifier()])) {
            Main::getInstance()->islandTop['island'][$this->getIdentifier()]['level'] = $this->level['level'];
            Main::getInstance()->islandTop['island'][$this->getIdentifier()]['size'] = $this->getSize();
            Main::getInstance()->islandTop['island'][$this->getIdentifier()]['mine'] = $this->mine;
            Main::getInstance()->islandTop['island'][$this->getIdentifier()]['money'] = $this->money['money'];
        }
    }

}