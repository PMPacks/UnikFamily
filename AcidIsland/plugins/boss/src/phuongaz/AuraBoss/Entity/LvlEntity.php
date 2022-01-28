<?php

namespace phuongaz\AuraBoss\Entity;

Class LvlEntity{

	private $level = [
		"health" => 20,
		"damage" => 1.5,
		"speed"	=> 1.5
	];

	public function getI4ByLevel(int $level){
		$curlvl = $this->level;
		$crhealth = $curlvl["health"]; 
		$crdamage = $curlvl["damage"];
		$crspeed = $curlvl["speed"];

		$name = "[Lvl. ".$level."] ";
		$health = $crhealth *$level;
		$damage = ($crdamage *$level)/5 + 2;
		$speed = $this->random_float(1, 5, 2);
		return [
			"tag" => $name,
			"health" => $health,
			"damage" => $damage,
			"speed" => $speed
		];
	}

	public function random_float($start_number = 0,$end_number = 1,$mul = 1000000)
	{
		if ($start_number > $end_number) return false;
		return mt_rand($start_number * $mul,$end_number * $mul)/$mul;
	}
}

