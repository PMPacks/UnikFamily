<?php

namespace TungstenVn\SeasonPass\sounds;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;

use pocketmine\Player;
use TungstenVn\SeasonPass\menuHandle\menuHandle;

class soundHandle {


    public $mnh;
    public function __construct(menuHandle $mnh) {
      $this->mnh = $mnh;
    }
    public function moveRightLeft(Player $player) {
      $this->livingRoom($player, "game.player.attack.strong");
    }
    public function normalTaken(Player $player) {
      $this->livingRoom($player, "random.levelup");
    }
    public function royalTaken(Player $player) {
      $this->livingRoom($player, "firework.twinkle");
    }
    public function illigelSound(Player $player) {
      $this->livingRoom($player, "mob.horse.angry");
    }
    public function alreadyTaken(Player $player) {
        $this->livingRoom($player, "mob.sheep.say");
    }
    public function dontHavePerm(Player $player) {
        $this->livingRoom($player, "mob.elderguardian.curse");
    }
    public function notEnoughLevel(Player $player) {
        $this->livingRoom($player, "mob.panda.bite");
    }
    public function water(Player $player) {
        $this->livingRoom($player, "random.totem");
    }
    public function livingRoom(Player $player,string $txt) {
      $sound = new PlaySoundPacket();
      $sound->x = $player->getX();
      $sound->y = $player->getY();
      $sound->z = $player->getZ();
      $sound->volume = 1;
      $sound->pitch = 1;
      $sound->soundName = $txt;
      $this->mnh->cmds->ssp->getServer()->broadcastPacket([$player], $sound);
    }
}