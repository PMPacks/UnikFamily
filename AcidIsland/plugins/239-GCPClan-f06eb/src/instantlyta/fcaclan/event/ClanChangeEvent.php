<?php


namespace instantlyta\fcaclan\event;


use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class ClanChangeEvent extends PlayerEvent
{
    public static $handlerList = null;

    protected $clanName;

    public function __construct(Player $player, string $clanName)
    {
        $this->player = $player;
        $this->clanName = $clanName;
    }
}