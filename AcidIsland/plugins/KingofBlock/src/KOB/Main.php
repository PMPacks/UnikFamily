<?php

namespace KOB;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase as PB;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\command\{Command, CommandSender};
use pocketmine\{Player, Server};
use pocketmine\item\Item;
use pocketmine\block\Block;
use KOB\task\OffKOB;
class Main extends PB implements Listener
{

    /** @Var Config */
    private $cfg;
    /** @var Main */
    public static $api;
    public $time;

    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->time = new Config($this->getDataFolder() . "time.yml", Config::YAML);
        $this->getScheduler()->scheduleRepeatingTask(new OffKOB($this), 20 * 60);
        self::$api = $this;
    }

    public static function getAPI(): Main
    {
        return self::$api;
    }
	public function onMove(PlayerMoveEvent $event){
		$player = $event->getPlayer();
		if($this->config->get(strtolower($player->getName())) == "on"){
			if(!$player->hasPermission("kob.cmd")){
		 $this->config->set(strtolower($player->getName()), "off");
        $this->config->save();
		$all = $this->time->getAll();
        unset($all[strtolower($player->getName())]);
        $this->time->setAll($all);
        $this->time->save();
        $player->sendMessage("§l§3●§e KingOfBlock đã Tắt.");
	}
	}
	}
      public function onCommand(CommandSender $s, Command $cmd, string $label, array $args):bool
    {   
        if($cmd->getName() == 'kingofblock'){
            if (!$s->hasPermission("kob.cmd")) {
$s->sendMessage("§cYou do not have permission to use this command");
return true;
}else{
            $this->formopen($s);
        }
        return true;
    }
}
    public function formopen(Player $player)
    {
        $form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $player, $data) {
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    $this->config->set(strtolower($player->getName()), "on");
                    $this->config->save();
                    $this->time->set(strtolower($player->getName()), 30);
                    $this->time->save();
                    $this->Message($player, false, "§l§3●§e KingOfBlock đã bật.");
                    break;
                case 2:
                    $this->config->set(strtolower($player->getName()), "off");
                    $this->config->save();
                    $all = $this->time->getAll();
                    unset($all[strtolower($player->getName())]);
                    $this->time->setAll($all);
                    $this->time->save();
                    $this->Message($player, false, "§l§3●§e KingOfBlock đã Tắt.");
                    break;
            }
        });
        $form->setTitle("§l§6KingOfBlock");
        $form->addButton("§l§3●§e Exit §3●\n§l§b「 §cThoát 」");
        $form->addButton("§l§3●§e On §3●\n§l§b「 §cBật 」");
        $form->addButton("§l§3●§e Off §3●\n§l§b「 §cTắt 」");
        $form->sendToPlayer($player);
        return $form;
    }
    private function Message(Player $player, $nextform = false, $msg)
    {
        if ($nextform !== false) {
            $form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $player, $data) {
                if ($data == null) return false;
                    switch ($data){
                        case 0:
                            $nextform;
                            break;
                        case 1:
                            break;
                }
            });
            $form->setTitle("§l§6KingOfBlock");
            $form->setContent($msg);
            $form->addButton("§l§3● §eTiếp tục §3●");
            $form->addButton("§l§3● §eThoát §3●");
            $form->sendToPlayer($player);
            return true;
        }
        $form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $player, $data){
            if($data == null) return false;
            switch ($data){
                case 0:
                    break;
                case 1:
                    break;
            }
        });
        $form->setTitle("§l§6KingOfBlock");
        $form->setContent($msg);
        $form->addButton("§l§3● §eThoát §3●");
        $form->sendToPlayer($player);
    }
}