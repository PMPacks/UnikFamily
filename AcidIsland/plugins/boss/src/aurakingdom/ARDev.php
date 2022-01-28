<?php 

namespace aurakingdom;

use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;
use pocketmine\Player;

use aurakingdom\manager\SkinManager;
use aurakingdom\manager\EnchantManager;
use aurakingdom\count\Calendar;
Class ARDev {

	/**
	* @param CommandSender|Player $sender
	* @param string $cmd Command want use
	* @param string $type Type command want use
	* @return void
	* @throws ARException
	*/
	public static function dispatch($sender, string $cmd, string $type = "console") : void {
		if(is_null($sender)){
			throw new ARException("Data sender is null", E_WARNING);
			return;
		}
		switch($type){
			case "console":
				Sever::getInstance()->dispatchCommand(new ConsoleCommandSender(), $cmd);
			break;
			case "player":
				Sever::getInstance()->dispatchCommand($sender, $cmd);
			break;
		}
	}

	/**
	* @return Calendar
	*/
	public static function getCalendar() :Calendar{
		return new Calendar();
	}

	/**
	* @param string $path Path file
	* @return SkinManager
	*/
	public static function getSkinManager($path) : SkinManager {
		return new SkinManager();
	}

	/**
	* @return EnchantManager
	*/
	public static function EnchantManager() : EnchantManager {
		return new EnchantManager();
	}

	/**
	* @param string $name Plugin name
	*/
	public static function getPlugin(string $name) {
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($name);
		if(is_null($plugin)){
			throw new ARException("Plugin $name not found", E_WARNING);
			return null;
		}
		return $plugin;
	}
}