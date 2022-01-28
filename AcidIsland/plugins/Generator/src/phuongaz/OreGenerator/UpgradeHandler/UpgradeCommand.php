<?php

namespace phuongaz\OreGenerator\UpgradeHandler;

use pocketmine\Player;
use pocektmine\Server;
use pocketmine\command\{Command, CommandSender};
use pocketmine\item\Item;

Class UpgradeCommand extends Command{

	public function __construct(){
		parent::__construct("generator", "generator system");
	}

	public function execute(CommandSender $sender, string $label, array $args) :bool{
		if($sender instanceof Player){
			(new UpgradeForm($sender))->init();
		}
		return true;
	}
}