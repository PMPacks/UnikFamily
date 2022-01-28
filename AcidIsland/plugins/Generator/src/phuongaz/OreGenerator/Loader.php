<?php

namespace phuongaz\OreGenerator;

use pocketmine\plugin\PluginBase;
use pocketmine\tile\Tile;
use pocketmine\Server;
use phuongaz\OreGenerator\UpgradeHandler\UpgradeCommand;

Class Loader extends PluginBase{

	public function onEnable() :void{
		$authors = $this->getDescription()->getAuthors();
		if(!in_array('phuongaz', $authors) or $this->getDescription()->getName() !== 'OreGenerator'){
			$this->getLogger()->error('OreGenerator by phuongaz | Không được đổi author hoặc tên');
			Server::getInstance()->getPluginManager()->disablePlugin($this);
			return;
		}
		Server::getInstance()->getPluginManager()->registerEvents(new GenListener(), $this);
		Server::getInstance()->getCommandMap()->register("OreGenerator", new UpgradeCommand());
        Tile::registerTile(GeneratorTile::class, ["GeneratorTile"]);
	}
}