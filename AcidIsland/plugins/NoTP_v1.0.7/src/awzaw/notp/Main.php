<?php

namespace awzaw\notp;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Main extends PluginBase implements Listener {

    private $enabled;

    public function onEnable() {
        $this->enabled = [];
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $issuer, Command $cmd, string $label, array $args) : bool{
        if ((strtolower($cmd->getName()) == "khongtp") && !(isset($args[0])) && ($issuer instanceof Player) && ($issuer->hasPermission("khongtp.toggle") || $issuer->hasPermission("khongtp.toggle.self"))) {
            if (isset($this->enabled[strtolower($issuer->getName())])) {
                unset($this->enabled[strtolower($issuer->getName())]);
            } else {
                $this->enabled[strtolower($issuer->getName())] = strtolower($issuer->getName());
            }

            if (isset($this->enabled[strtolower($issuer->getName())])) {
                $issuer->sendMessage("§e Không tp đã được bật!");
            } else {
                $issuer->sendMessage("§e Không tp đã được tắt!");
            }
            return true;
        } else {
            return false;
        }
    }

     public function onPlayerCommand(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer();
		if ($player->hasPermission("khongtp.mf")) return;
        if ($event->isCancelled()) return;
        $message = $event->getMessage();
        if (strtolower(substr($message, 0, 3)) == "/tp" || strtolower(substr($message, 0, 9)) == "/teleport" || strtolower(substr($message, 0, 14)) == "/pocketmine:tp" || strtolower(substr($message, 0, 20)) == "/pocketmine:teleport") {
            $args = explode(" ", $message);
            if (!isset($args[1])) {
                return;    
            }
			if($args[1] == null){
				return;
			}
            $sender = $event->getPlayer();

            foreach ($this->enabled as $notpuser) {

                if ((strpos(strtolower($notpuser), strtolower($args[1])) !== false) && (strtolower($notpuser) !== strtolower($sender->getName()))) {
                    $sender->sendMessage(TextFormat::RED . "§e Người này không cho phép tp!");
                    $event->setCancelled(true);
                    return;
				}
				
				if (isset($args[2])){
					
				if($args[2] == null){
					return;
				}
				if(strpos(strtolower($notpuser), strtolower($args[2])) !== false && (strtolower($notpuser) !== strtolower($sender->getName()))) {
					$sender->sendMessage(TextFormat::RED . "§e Người này không cho phép tp!");
					$event->setCancelled(true);
					return;
				}
				}
			}
		}
	 }
}
						