<?php

namespace phuongaz\AuraBoss\Entity;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\block\Flowable;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\level\Position;
use pocketmine\entity\Living;

abstract class BossMain extends Human implements ArtificialIntelligence {

    const FIND_DISTANCE = 15;

    const LOSE_DISTANCE = 25;

    const ATTACK_DISTANCE = 2;

    /** @var int */
    public $attackDamage;

    /** @var float */
    public $speed;

    /** @var int */
    public $attackWait;

    /** @var int */
    public $regenerationWait = 0;

    /** @var int */
    public $regenerationRate;

    /** @var float[] */
    protected $damages = [];

    /** @var Player|null */
    private $target = null;

    /** @var int */
    private $findNewTargetTicks = 0;

    /** @var int */
    private $jumpTicks = 5;

    /** @var array */
    public $spawnPos;

    /** @var int*/
    private $range = 500;
    
    /**
     * BaseEntity constructor.
     *
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
    }

    /**
     * @return string
     */
    public function getSaveId(): string {
        return self::class;
    }

    /**
     * @param int $tickDiff
     *
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        parent::entityBaseTick($tickDiff);
        if(!$this->isAlive()) {
            if(!$this->closed) {
                $this->flagForDespawn();
            }
            return false;
        }
        if($this->regenerationWait-- <= 0) {
            $this->setHealth($this->getHealth() + $this->regenerationRate);
            $this->regenerationWait = 500;
        }
        if($this->distanceSquared($this->getSpawnPos()) > $this->range){
            $this->teleport($this->getSpawnPos());
            $this->target = null;
        }
        if($this->hasTarget()) {
            return $this->attackTarget();
        }
        if($this->findNewTargetTicks > 0) {
            $this->findNewTargetTicks--;
        }
        if(!$this->hasTarget() and $this->findNewTargetTicks === 0) {
            $this->findNewTarget();
        }
        if($this->jumpTicks > 0) {
            $this->jumpTicks--;
        }
        if(!$this->isOnGround()) {
            if($this->motion->y > -$this->gravity * 4) {
                $this->motion->y = -$this->gravity * 4;
            }
            else {
                $this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
            }
        }
        else {
            $this->motion->y -= $this->gravity;
        }
        $this->move($this->motion->x, $this->motion->y, $this->motion->z);
        if($this->shouldJump()) {
            $this->jump();
        }
        $this->updateMovement();
        return $this->isAlive();
    }

    /**
     * @return bool
     */
    public function attackTarget(): bool {
        $target = $this->getTarget();
        if($target == null or $target->distance($this) >= self::LOSE_DISTANCE) {
            $this->target = null;
            return true;
        }
        if($this->jumpTicks > 0) {
            $this->jumpTicks--;
        }
        if(!$this->isOnGround()) {
            if($this->motion->y > -$this->gravity * 4) {
                $this->motion->y = -$this->gravity * 4;
            }
            else {
                $this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
            }
        }
        else {
            $this->motion->y -= $this->gravity;
        }
        $this->move($this->motion->x, $this->motion->y, $this->motion->z);
        if($this->shouldJump()) {
            $this->jump();
        }
        $x = $target->x - $this->x;
        $y = $target->y - $this->y;
        $z = $target->z - $this->z;
        if($x * $x + $z * $z < 1.2) {
            $this->motion->x = 0;
            $this->motion->z = 0;
        }
        else {
            $this->motion->x = $this->getSpeed() * 0.15 * ($x / (abs($x) + abs($z)));
            $this->motion->z = $this->getSpeed() * 0.15 * ($z / (abs($x) + abs($z)));
        }
        $this->yaw = rad2deg(atan2(-$x, $z));
        $this->pitch = rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
        $this->move($this->motion->x, $this->motion->y, $this->motion->z);
        if($this->shouldJump()) {
            $this->jump();
        }
        if($this->distance($target) <= self::ATTACK_DISTANCE and $this->attackWait <= 0) {
            if($target->isCreative() or $target->isSpectator()){
                $this->target = null;
                return $this->isAlive();
            }
			$points = 1;
			if($target != null and $target instanceof Living){
				//$points = $target->getArmorPoints() !== 0 ? $target->getArmorPoints() : 1;				
			}
			
            $damage = $this->getBaseAttackDamage() / ($points * 1.5);
			
            if($target !== null and $target->getHealth() <= $damage) {
                $this->target = null;
                $this->findNewTarget();
            }
            $target->setHealth($target->getHealth() - $damage);
            $deltaX = $target->x - $this->x;
            $deltaZ = $target->z - $this->z;
			if($target !== null){
				//$target->knockBack($this, $this->getBaseAttackDamage(), $deltaX, $deltaZ);
				$target->doHitAnimation();
				$this->broadcastEntityEvent(ActorEventPacket::ARM_SWING);
				$this->attackWait = 20;
			}
        }
        $this->updateMovement();
        $this->attackWait--;
        return $this->isAlive();
    }

    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void {
        if($source instanceof EntityDamageByEntityEvent) {
            $killer = $source->getDamager();
            if($killer instanceof Player) {
                if($killer->isFlying() or $killer->getAllowFlight() == true) {
                    $killer->setFlying(false);
                    $killer->setAllowFlight(false);
                }
                if($this->target === null or $this->target->getName() != $killer->getName() and !$killer->isCreative()) {
                    $this->target = $killer;
                }
                if(isset($this->damages[$killer->getName()])) {
                    $this->damages[$killer->getName()] += $source->getFinalDamage();
                }
                else {
                    $this->damages[$killer->getName()] = $source->getFinalDamage();
                }
            }
        }
        parent::attack($source);
    }

    /**
     * @param Entity $attacker
     * @param float $damage
     * @param float $x
     * @param float $z
     * @param float $base
     */
    public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4): void {
        parent::knockBack($attacker, $damage, $x, $z, $base * 2);
    }

    public function findNewTarget() {
        $distance = self::FIND_DISTANCE;
        $target = null;
        foreach($this->getLevel()->getPlayers() as $player) {
            if($player instanceof Player and $player->distance($this) <= $distance and (!$player->isCreative())) {
                $distance = $player->distance($this);
                $target = $player;
            }
        }
        $this->findNewTargetTicks = 60;
        $this->target = ($target != null ? $target : null);
    }

    /**
     * @return bool
     */
    public function hasTarget(): bool {
        $target = $this->getTarget();
        if($target == null) {
            return false;
        }
        return true;
    }

    /**
     * @return Player|null
     */
    public function getTarget(): ?Player {
        return $this->target;
    }

    /**
    * @return Postion
    */
    public function getSpawnPos(): Position{
        return new Position($this->spawnPos["x"], $this->spawnPos["y"], $this->spawnPos["z"], $this->level);
    }

    /**
     * @return float
     */
    public function getSpeed(): float {
        return ($this->isUnderwater() ? $this->speed / 2 : $this->speed);
    }

    /**
     * @return int
     */
    public function getBaseAttackDamage(): int {
        return $this->attackDamage;
    }

    /**
     * @param float $y
     *
     * @return Block
     */
    public function getFrontBlock($y = 0.0): Block {
        $dv = $this->getDirectionVector();
        $pos = $this->asVector3()->add($dv->x * $this->getScale(), $y + 1, $dv->z * $this->getScale())->round();
        return $this->getLevel()->getBlock($pos);
    }

    /**
     * @return bool
     */
    public function shouldJump(): bool {
        if($this->jumpTicks > 0) {
            return false;
        }
        return $this->isCollidedHorizontally or
            ($this->getFrontBlock()->getId() != 0 or $this->getFrontBlock(-1) instanceof Stair) or
            ($this->getLevel()->getBlock($this->asVector3()->add(0, -0, 5)) instanceof Slab and
                (!$this->getFrontBlock(-0.5) instanceof Slab and $this->getFrontBlock(-0.5)->getId() != 0)) and
            $this->getFrontBlock(1)->getId() === 0 and
            $this->getFrontBlock(2)->getId() === 0 and
            !$this->getFrontBlock() instanceof Flowable and
            $this->jumpTicks == 0;
    }

    /**
     * @return int
     */
    public function getJumpMultiplier(): int {
        if($this->getFrontBlock() instanceof Slab or $this->getFrontBlock() instanceof Stair or
            $this->getLevel()->getBlock($this->asVector3()->subtract(0, 0.5)->round()) instanceof Slab and
            $this->getFrontBlock()->getId() != 0) {
            $fb = $this->getFrontBlock();
            if($fb instanceof Slab and $fb->getDamage() > 0) {
                return 8;
            }
            if($fb instanceof Stair and $fb->getDamage() >  0) {
                return 8;
            }
            return 4;
        }
        return 16;
    }

    public function jump(): void {
        $this->motion->y = $this->gravity * $this->getJumpMultiplier();
        $this->move($this->motion->x * 1.25, $this->motion->y, $this->motion->z * 1.25);
        $this->jumpTicks = 5;
    }
}