<?php

namespace phuongaz\AuraBoss\Entity\events;


use pocketmine\Server;
use pocketmine\Player;

use pocketmine\entity\Entity;

use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Cancellable;

Class BossDeathEvent extends EntityEvent implements Cancellable{

	protected $entity;

	private $damager;

	public function __construct(Entity $entity, Entity $damager){
		$this->entity = $entity;
		$this->damager = $damager;
	}

	public function getDamager() :Entity{
		return $this->damager;
	}
}