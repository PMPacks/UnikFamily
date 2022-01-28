<?php

namespace SoulWell\ZulfahmiFjr;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use InvMenu\muqsit\invmenu\InvMenuHandler;
use SoulWell\ZulfahmiFjr\task\RollUpdater;

class Main extends PluginBase implements Listener{

    public $set = array();

    public function onEnable(){
     if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
      $this->getServer()->getPluginManager()->registerEvents($this, $this);
      if(!InvMenuHandler::isRegistered()){
       InvMenuHandler::register($this);
      }
      $this->saveResource("config.yml");
      $this->wellItems = $this->getConfig()->get("items");
      $this->souls = new Config($this->getDataFolder().'souls.yml', Config::YAML);
      $this->getLogger()->info("Crate By PIG");
     }

    public function onPlayerJoin(PlayerJoinEvent $e){
     $p = $e->getPlayer();
     if($p instanceof Player){
      if(!$this->souls->exists($p->getLowerCaseName())){
       $this->souls->set($p->getLowerCaseName(), 0);
       $this->souls->save();
      }
      $data = $this->getConfig();
      $x = $data->get("well.x");
      $y = $data->get("well.y");
      $z = $data->get("well.z");
      $text = "§l§3▶§a Hòm §eLucky §3◀";
      $p->getLevel()->addParticle(new FloatingTextParticle(new Vector3($x + 0.5, $y + 2, $z + 0.5), '', $text), array($p));
     }
    }

    public function onPlayerQuit(PlayerQuitEvent $e){
     $this->souls->save();
    }

    public function onPlayerInteract(PlayerInteractEvent $e){
     $p = $e->getPlayer();
     $b = $e->getBlock();
     if($p instanceof Player){
      $data = $this->getConfig();
      $x = $data->get("well.x");
      $y = $data->get("well.y");
      $z = $data->get("well.z");
      if($b->x === $x && $b->y === $y + 2 && $b->z === $z || $b->x === $x && $b->y === $y + 1 && $b->z === $z){
       $pk = new ModalFormRequestPacket();
       $pk->formId = 7382999;
       $message = "";
       foreach($this->getConfig()->get("message") as $text){
        $text = str_replace(["{KEY}", "{PLAYER}"], [$this->souls->get($p->getLowerCaseName()), $p->getName()], $text);
        $message .= "{$text}§r\n\n";
       }
       $encode = ["type" => "form", "title" => "§l§9⚫§a Crate Lucky §9⚫", "content" => "{$message}", "buttons" => [["text" => "§l§e•§a Quay Crate Lucky §e•"], ["text" => "§l§e•§c Thoát §e•"]]];
       $data = json_encode($encode);
       $pk->formData = $data;
       $p->dataPacket($pk);
       $e->setCancelled();
      }
     }
    }

    public function onBlockBreak(BlockBreakEvent $e){
     $p = $e->getPlayer();
     $b = $e->getBlock();
     if(isset($this->set[$p->getName()])){
      $x = $b->getX();
      $y = $b->getY();
      $z = $b->getZ();
      $data = $this->getConfig();
      if(empty($data->get("well.x")) && empty($data->get("well.y")) && empty($data->get("well.z"))){
       $data->set("well.x", $x);
       $data->set("well.y", $y);
       $data->set("well.z", $z);
       $data->save();
       $text = "§l§3▶§a Hòm §eLucky §3◀";
       $b->getLevel()->addParticle(new FloatingTextParticle(new Vector3($x + 0.5, $y + 2, $z + 0.5), '', $text));
       $b->getLevel()->setBlockIdAt($x, $y + 1, $z, 120);
       $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Crate Lucky đã được tạo");
       unset($this->set[$p->getName()]);
      }else{
       $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Crate Lucky đã được tạo, vui lòng xóa Crate Lucky đầu tiên");
       unset($this->set[$p->getName()]);
      }
      $e->setCancelled();
      return;
     }
     $data = $this->getConfig();
     if(!empty($data->get("well.x")) && !empty($data->get("well.y")) && !empty($data->get("well.z"))){
      $x = $data->get("well.x");
      $y = $data->get("well.y");
      $z = $data->get("well.z");
      if($b->x === $x && $b->y === $y + 2 && $b->z === $z || $b->x === $x && $b->y === $y + 1  && $b->z === $z){
       if($p->isOP()){
        $data->remove("well.x");
        $data->remove("well.y");
        $data->remove("well.z");
        $data->save();
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Crate Lucky đã bị phá vỡ thành công");
       }else{
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§c Bạn không có quyền phá vỡ Crate Lucky");
        $e->setCancelled();
       }
      }
     }
    }

    public function onPacketReceive(DataPacketReceiveEvent $e){
     $pk = $e->getPacket();
     $p = $e->getPlayer();
     if($pk instanceof ModalFormResponsePacket){
      $id = $pk->formId;
      $data = json_decode($pk->formData, true);
      if($id === 7382999){
       if(isset($data)){
        if($data === 0){
         if($this->souls->get($p->getLowerCaseName()) < 1){
          $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§c Bạn không có Key Lucky để mở Crate Lucky");
          return;
         }
         $this->souls->set($p->getLowerCaseName(), $this->souls->get($p->getLowerCaseName()) - 1);
         $this->souls->save();
         $this->getScheduler()->scheduleRepeatingTask(new RollUpdater($this, $p, 100), 3);
        }
       }
      }
     }
    }

    public function onCommand(CommandSender $p, Command $command, string $label, array $args):bool{
     switch($command->getName()){
      case "addcrate":{
       if(!$p instanceof Player){
        $p->sendMessage("Xin hãy dùng lệnh trong game..!");
        return false;
       }
       if(!$p->isOP()){
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§c Bạn không được quyền dùng lệnh này.");
        return false;
       }
       $this->set[$p->getName()] = true;
       $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Hãy đập 1 block bất kì để tạo Crate Lucky.");
       break;
      }
      case "addkey":{
       if(!$p->isOP()){
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§c Bạn không được quyền dùng lệnh này.");
        return false;
       }
       if(!isset($args[0])){
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Sử dụng: §a/addkey §f(§e Tên §f)");
        return false;
       }
       if(!isset($args[1])){
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Sử dụng: §a/addkey §f(§e Tên §f) §f(§e Số lượng §f)");
        return false;
       }
       if(!is_numeric($args[1]) && $args[1] <= 0){
        $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§c Vui lòng nhập chính xác");
        return false;
       }
       $t = $this->getServer()->getPlayer($args[0]);
       if($t instanceof Player){
        $t->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Bạn đã nhận §e".$args[1]."§b Key Lucky");
        $name = $t->getLowerCaseName();
       }else{
        $name = strtolower($args[0]);
       }
       $this->souls->set($name, $this->souls->get($name) + $args[1]);
       $this->souls->save();
       $p->sendMessage("§l§c【 §fvιcтoʀʏ §eoғ §6ʟᴇԍᴇɴᴅ §c】§b Người chơi §e".$name."§b đã nhận §e".$args[1]."§b Key Lucky");
       break;
      }
     }
     return true;
    }

}