<?php

declare(strict_types=1);

namespace minefore\mf;

use pocketmine\plugin\PluginBase as AltayBase;
use pocketmine\command\{Command as CMD, CommandSender as CS};
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\Listener;
use minefore\mf\task\OffTask;
class Main extends AltayBase implements Listener{

	/** @var Config */
	public $cfg;

	/** @var Main */
    public static $api;
	public $con;
    public function onEnable(){
    	@mkdir($this->getDataFolder());
    	$this->getServer()->getPluginManager()->registerEvents(new Eventss($this), $this);
    	$this->cfg = new Config($this->getDataFolder()."data.yml", Config::YAML);	
		$this->cons = new Config($this->getDataFolder()."cons.yml", Config::YAML);
		$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$this->getScheduler()->scheduleRepeatingTask(new OffTask($this), 20*60);		
    	self::$api = $this;
    }
    public function onCommand(CS $s, CMD $cmd, string $label, array $args):bool
    {	
		if($cmd->getName() == 'eternity'){
			if (!$s->hasPermission("eternity.command")) {
$s->sendMessage("§cYou do not have permission to use this command");
return true;
}else{
if (!$s instanceof Player) {
$s->sendMessage("Vui Lòng Sử Dụng Lệnh Trong Trò Chơi");
return true;
}
			$this->mainForm($s);
		}
		return true;
    }
}
    public static function getAPI():Main
    {
    	return self::$api;
    }
	
	private function mainForm(Player $player){
		$form = $this->formapi->createSimpleForm(function (Player $sender, $data){

		$result = $data;
		if ($result == null) {
		}
		switch ($result) {
			case 1:
               $this->cfg->set(strtolower($sender->getName()), "on");
               $this->cfg->save();
               $this->cons->set(strtolower($sender->getName()), 30);
               $this->cons->save();
               $this->msgForm($sender,false, "§l§3●§e Đã Bật tính năng đóng băng block trong vòng 30 phút!");				
			break;
			
			case 2:
               $this->cfg->set(strtolower($sender->getName()), "off");
               $this->cfg->save();
				$all = $this->cons->getAll();
				unset($all[strtolower($sender->getName())]);
			   $this->cons->setAll($all);
               $this->cons->save();			   
               $this->msgForm($sender,false, "§l§3●§e Đã tính năng đóng băng block Tắt");				
			break;	
			
			case 3:
				
			break;
		}
		});
		$form->setTitle("§l§6Eternity Sytem");
		$form->addButton("§l§3●§a Exit §3●\n§l§b「 §cThoát 」");
		$form->addButton("§l§3●§a Bật §3●\n§l§b「§c Bật 」");
		$form->addButton("§l§3●§a Tắt §3●\n§l§b「 §cTắt 」");
		$form->sendToPlayer($player);		
	}	
	
	private function msgForm(Player $player, $nextform = false, $msg){
		if($nextform !== false){
			$form = $this->formapi->createSimpleForm(function (Player $p, $data) {
				if($data === NULL) return false;	
				
					switch($data){
						case 0:
							$nextform;						
						break;
						
						case 1:

						break;
					}
			
			});
		    $form->setTitle("§l§eEternity");
			$form->setContent($msg);
			$form->addButton("§l§3● §aTiếp tục §3●");
			$form->addButton("§l§3● §aThoát §3●");		
			$form->sendToPlayer($player);	
			return true;
		}
		
		
        $form = $this->formapi->createSimpleForm(function (Player $p, $data) {

			if($data === NULL) return false;	
			
				switch($data){
					case 0:
					
					break;
					
					case 1:

					break;
					
				}
		
		});
		$form->setTitle("§l§eEternity");
        $form->setContent($msg);
		$form->addButton("§l§3● §aThoát §3●");		
        $form->sendToPlayer($player);			
	}	
}