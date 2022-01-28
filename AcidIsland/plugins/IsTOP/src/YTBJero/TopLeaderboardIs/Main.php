<?php

namespace YTBJero\TopLeaderboardIs;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;

class Main extends PluginBase{

    private $particles = [];

    public function onEnable(){
     $this->config = (new Config($this->getDataFolder()."config.yml", Config::YAML))->getAll();
     if(empty($this->config["positions"])){
      $this->getServer()->getLogger()->Info("[Money Board] Plugin Enable! Please specify the position for the money board In-Game!");
      return;
     }
     $pos = $this->config["positions"];
     $this->particles[] = new FloatingText($this, new Vector3($pos[0], $pos[1], $pos[2]));
     $this->getScheduler()->scheduleRepeatingTask(new UpdateTask($this), 100);
     $this->getServer()->getLogger()->Info("[Money Board] The location is loaded...");
    }

    public function onCommand(CommandSender $p, Command $command, string $label, array $args):bool{
     if($command->getName() === "sib"){
      if(!$p instanceof Player) return false;
      if(!$p->isOp()) return false;
      $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
      $config->set("positions", [round($p->getX()), round($p->getY()), round($p->getZ())]);
      $config->save();
      $p->sendMessage("§7[§aMoney Board§7] §bTop Money Leaderboard location has been determined, §cPlease reload the server !");
     }
     return true;
    }

    public function getLeaderBoard():string{
     $data = $this->getServer()->getPluginManager()->getPlugin("DLevelIsland");
     $money_top = $data->level->getAll();
     $message = "";
     $topmoney = "§e§l[ §fXếp Hạng Island Trong Máy Chủ Island §e ]";
     if(count($money_top) > 0){
      arsort($money_top);
      $i = 0;
      foreach($money_top as $name => $money){
       $message .= "\n§l§bHạng§e ".($i+1)." §bthuộc về§c ".$name." §fvới§e ".$money."§a$"."\n\n\n";
       if($i >= 10){
        break;
       }
       ++$i;
      }
     }
     $return = (string) $topmoney.$message;
     return $return;
    }

    public function getParticles():array{
     return $this->particles;
    }

}