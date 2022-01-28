<?php

namespace Fly;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\command\{Command,CommandSender, CommandExecutor, ConsoleCommandSender};
use jojoe77777\FormAPI;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat as TF;
class Main extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->cfg = new Config($this->getDataFolder()."config.yml", Config::YAML);
    }
    public function onJoin(PlayerJoinEvent $ev){
        $user = $ev->getPlayer()->getName();
        if(!$this->cfg->exists($user)) {
            $this->cfg->set($user, false);
            $this->cfg->save();
        }
    }


    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool
    {
        if (strtolower($cmd->getName()) === "fly") {
        if (!$sender->hasPermission("fly.command")){
            $sender->sendMessage(TF::RED . "You don't have permission to use this command!");
            return false;
        } else{
            $this->FormFly($sender);
        }
        return true;
        }
    }

    public function FormFly(Player $sender)
    {
        $formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $formapi->createCustomForm(function(Player $sender, $data){
            if($data === null){
                return true;
            }
    if($data[0] == true){
        $this->cfg->set($sender->getName(), true);
        $this->cfg->save();
        $sender->setAllowFlight(true);
        $sender->sendMessage("§l§3●§e Đã Bật Fly");
    }
    if($data[0] == false){
        $this->cfg->set($sender->getName(), false);
        $this->cfg->save();
        $sender->setAllowFlight(false);
        $sender->sendMessage("§l§3●§e Đã Tắt Fly");
    }
    });
$form->setTitle("§l§6Fly Sytem");
$form->addToggle("§l§3●§e Bật/Tắt Fly", $this->cfg->get($sender->getName()));
$form->sendToPlayer($sender);
return $form;
}
}