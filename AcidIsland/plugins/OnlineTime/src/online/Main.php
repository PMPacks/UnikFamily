<?php

namespace online;

use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\{
    PlayerJoinEvent,
    PlayerQuitEvent
};
use pocketmine\command\{
    Command,
    CommandSender,
};
use pocketmine\scheduler\ClosureTask;
use online\TimeTask;

class Main extends PluginBase implements Listener{
	
	public $data;
	
	public function onEnable():void{
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
	    $this->getLogger()->info("§l§a> §bPlugin OnlineTime By LetTIHL");
	    $this->saveResource("data.yml");
        $this->data = yaml_parse(file_get_contents($this->getDataFolder() . "data.yml"));
        $this->getScheduler()->scheduleRepeatingTask(new TimeTask($this), 20);
        
    }
    
    public function onQuit(PlayerQuitEvent $ev){
        file_put_contents($this->getDataFolder() . "data.yml", yaml_emit($this->data));
    }
    
    public function onDisable(){
        file_put_contents($this->getDataFolder() . "data.yml", yaml_emit($this->data));
        sleep(3);
    }

    public function create(Player $player){
        if(!isset($this->data["time"][strtolower($player->getName())])){
        $this->data["time"][strtolower($player->getName())] = 0;
        }
    }
    
    public function addData(Player $player){
        $this->data["time"][strtolower($player->getName())]++;
    }
    
    public function getData(Player $player){
        return $this->data["time"][strtolower($player->getName())];
    }
    
	public function onCommand(CommandSender $player, Command $command, String $label, array $args) : bool {
        if($command->getName() == "toponline")$this->TopTime($player);
        return true;
	}
	public function TopTime(Player $player){
		$time = $this->data["time"];
		$message = ""; $rank = "100+";
		if(count($time) > 0){
			arsort($time);
			$i = 1;
			foreach($time as $name => $s){
			    $h = floor ($s/3600); 
			    $s -= $h*3600;
                $m = floor ($s/60); 
                $s -= $m*60;
			    if($i == 1) $message .= "§l§bHạng§e $i §bthuộc về§c $name §fvới§e $h Tiếng $m Phút $s Giây\n";
			    if($i == 2) $message .= "§l§bHạng§e $i §bthuộc về§c $name §§fvới§e $h Tiếng $m Phút $s Giây\n";
			    if($i == 3) $message .= "§l§bHạng§e $i §bthuộc về§c $name §fvới§e $h Tiếng $m Phút $s Giây\n";
			    if($i > 3) $message .= "§l§bHạng§e $i §bthuộc về§c $name §f với§e $h Tiếng $m Phút $s Giây\n";
				if($name == strtolower($player->getName()))$rank = $i;
				if($i >= 100) break;
				++$i;
			}
		}
		$s = $this->getData($player);
		$h = floor ($s/3600); 
		$s -= $h*3600;
        $m = floor ($s/60); 
        $s -= $m*60;
		
		$form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createSimpleForm(function (Player $sender, $data ){});
		$form->setTitle("§l§c⚒§6 Xếp Hạng Trực Tuyến §c ⚒");
		$form->setContent("§3•§l§a Xếp hạng của bạn :§f $rank \n".$message);
		$form->addButton("§l§3• §aThoát §3•");
		$form->sendToPlayer($player);
		return $form;
	}
}