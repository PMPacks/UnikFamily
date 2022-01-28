<?php

namespace NV;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\event\player\{PlayerInteractEvent, PlayerItemHeldEvent, PlayerJoinEvent, PlayerChatEvent};
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\level\particle\{AngryVillagerParticle,BubbleParticle,CriticalParticle,DestroyBlockParticle,DustParticle,EnchantmentTableParticle,EnchantParticle,EntityFlameParticle,LargeExplodeParticle,FlameParticle,HappyVillagerParticle,HeartParticle,InkParticle,InstantEnchantParticle,ItemBreakParticle,LavaDripParticle,LavaParticle,MobSpellParticle,PortalParticle,RainSplashParticle,RedstoneParticle,SmokeParticle,SpellParticle,SplashParticle,SporeParticle,TerrainParticle,WaterDripParticle,WaterParticle,WhiteSmokeParticle};
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\Inventory;
use pocketmine\level\Level;
use pocketmine\entity\human;
use pocketmine\entity\Effect;
use pocketmine\network\protocol\SetTitlePacket;
use slapper\entities\SlapperHuman;
use slapper\events\SlapperHitEvent;

class Main extends PluginBase implements Listener{

public $money;
public $data;
private $config, $amount;

public function onEnable(){
if(!file_exists($this->getDataFolder() . "Otake")){
@mkdir($this->getDataFolder(). "Otake");
}
$this->quest = new Config($this->getDataFolder() . "Otake" . "otake.yml", Config::YAML);
$this->saveDefaultConfig();
$this->config = $this->getConfig();
$this->config->save();
$this->money = EconomyAPI::getInstance();
$this->shigeotoku = $this->getServer()->getPluginManager()->getPlugin("Shigeootoku");
$this->nv = $this->getServer()->getPluginManager()->getPlugin("NGVS_Quest");

$this->getServer()->getPluginManager()->registerEvents($this,$this);
}
public function onJoin(PlayerJoinEvent $event){
if(!($this->quest->exists(strtolower($event->getPlayer()->getName())))){
$this->quest->set(strtolower($event->getPlayer()->getName()), ["1" => "Not", "2" => "Not", "3" => "Not", "4" => "Not"]);
$this->quest->save();
return true;
}
}
public function get1(Player $player){
$get1 = $this->quest->get(strtolower($player->getName()))["1"];
return $get1;
}
public function get2(Player $player){
$get2 = $this->quest->get(strtolower($player->getName()))["2"];
return $get2;
}
public function get3(Player $player){
$get3 = $this->quest->get(strtolower($player->getName()))["3"];
return $get3;
}

public function setDone1(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Not", "3" => "Not"]);
$this->quest->save();
}
public function setDone2(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Not"]);
$this->quest->save();
}
public function setDone3(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done"]);
$this->quest->save();
}
public function onTap(SlapperHitEvent $ev){
$entity = $ev->getEntity();
$damager = $ev->getDamager();
if(!$entity instanceof SlapperHuman && $entity->getName() ==  "§l§e【§c!§e】§r§f Mitsuhi Otake"){
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Mitsuhi Otake" && $this->get1($damager) == "Not" && $this->get2($damager) == "Not" && $this->get3($damager) == "Not"){
$this->UIquest1($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Mitsuhi Otake" && $this->get1($damager) == "Done" && $this->get2($damager) == "Not" && $this->get3($damager) == "Not"){
$this->UIquest2($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Mitsuhi Otake" && $this->get1($damager) == "Done" && $this->get2($damager) == "Done" && $this->get3($damager) == "Not"){
$this->UIquest3($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Mitsuhi Otake" && $this->shigeotoku->get8($damager) == "Done" && $this->get2($damager) == "Done" && $this->get3($damager) == "Not"){
$this->UIquest4($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§3Mitsuhi Otake" && $this->get2($damager) == "Done" && $this->get3($damager) == "Done"){
$this->UIthongbao($damager, "§aBạn Đã Hoàn Thành Nhiệm Vụ ✓ !");
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Mitsuhi Otake" && $this->shigeotoku->get8($damager) == "Not" && $this->get2($damager) == "Not"){
$this->UIthongbao($damager, "§f•§a Vui lòng hoàn thành nhiệm vụ theo số thứ tự!");
return true;
}

}
public function UIthongbao($player, $message){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null) use ($message){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§f Mitsuhi Otake");
$form->setContent($message);
$form->addButton("§l§1Đã Hiểu");
$form->sendToPlayer($player);
return $form;
}
public function UIquest1($player){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->UIquest2($player);
$this->setDone1($player);
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§f Mitsuhi Otake");
$form->setContent("§fXin chào, ".$player->getName());
$form->addButton("§l§1Giúp Đỡ");
$form->sendToPlayer($player);
return $form;
}
public function UIquest2($player){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->UIquest3($player);
$this->setDone2($player);
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§f Mitsuhi Otake");
$form->setContent("§fGiúp Mitsuhi Otake Hoàn Thành Thử Thác Của Shigeo Toku!.");
$form->addButton("§l§1Đồng Ý");
$form->sendToPlayer($player);
return $form;
}
public function UIquest3($player){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->setDone3($player);
 $player->addTitle("§l§a【§6√§a】\n§r§3Mitsuhi Otake Đã Xong!");
$this->money->addMoney($player->getName(), 1000);
$box = Item::get(264, 0, 32);
$player->getInventory()->addItem($box);
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§f Mitsuhi Otake");
$form->setContent("§fCảm Ơn Bạn Đã Giúp Đỡ Tôi. Đây là Phần Thưởng§7:\n§7- §f1.000 Xu\n§7- §f1 Key Daily\n§7- §f32 Viên Kim Cương");
$form->addButton("§l§1Nhận");
$form->sendToPlayer($player);
return $form;
     }
/*public function onFight(EntityDamageEvent $event) {
     if($event instanceof EntityDamageByEntityEvent) {
     $hit = $event->getEntity();
     $damager = $event->getDamager();
     if($damager instanceof Player) {
     if($hit instanceof SlapperHuman && !$event->isCancelled()){
     $event->setCancelled(true);
     }
     }
     }
}*/
}