<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝



namespace BaliGamerz\SkyBlock\Entity;

use pocketmine\entity\Skin;
use BaliGamerz\SkyBlock\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\Explosive;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class SkyBlockTNT extends Entity implements Explosive
{
    public const NETWORK_ID = self::TNT;

    public $width = 0.98;
    public $height = 0.98;

    protected $baseOffset = 0.49;

    protected $gravity = 0.04;
    protected $drag = 0.02;

    public $mircotime;

    public $type = "§b";

    /** @var int */
    protected $fuse;

    public $canCollide = false;

    public function attack(EntityDamageEvent $source): void
    {
        if ($source->getCause() === EntityDamageEvent::CAUSE_VOID) {
            parent::attack($source);
        }
    }

    protected function initEntity(): void
    {
        parent::initEntity();

        if ($this->namedtag->hasTag("Fuse", ShortTag::class)) {
            $this->fuse = $this->namedtag->getShort("Fuse");
        } else {
            $this->fuse = 80;
        }

        $this->setGenericFlag(self::DATA_FLAG_IGNITED, true);
        $this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuse);

        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_IGNITE);
    }

    public function canCollideWith(Entity $entity): bool
    {
        return false;
    }

    public function saveNBT(): void
    {
        parent::saveNBT();
        $this->namedtag->setShort("Fuse", $this->fuse, true); //older versions incorrectly saved this as a byte
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        foreach ($this->getViewers() as $player) {
            switch ($this->fuse) {
                case 80;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 70;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 60:
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 50;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 40;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 30;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 20;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 15;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 10;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
                case 5;
                case 4;
                case 3;
                case 2;
                case 1;
                    Utils::addSound($player, 2, 6, 'note.bit');
                    break;
            }
        }
        $this->setNameTag($this->type . round($this->mircotime - microtime(true), 3) . "\n");
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->fuse % 5 === 0) { //don't spam it every tick, it's not necessary
            $this->propertyManager->setInt(self::DATA_FUSE_LENGTH, $this->fuse);
        }

        if (!$this->isFlaggedForDespawn()) {
            $this->fuse -= $tickDiff;

            if ($this->fuse <= 0) {
                $this->flagForDespawn();
                $this->explode();
            }
        }

        return $hasUpdate or $this->fuse >= 0;
    }

    public function explode(): void
    {
        $ev = new ExplosionPrimeEvent($this, 4);
        $ev->call();
        if (!$ev->isCancelled()) {
            $explosion = new Explosion(Position::fromObject($this->add(0, $this->height / 2, 0), $this->level), $ev->getForce(), $this);
            if ($ev->isBlockBreaking()) {
                $explosion->explodeA();
            }
            $explosion->explodeB();
        }
    }
}