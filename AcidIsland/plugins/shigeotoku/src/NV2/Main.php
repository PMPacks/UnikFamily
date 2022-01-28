<?php

namespace NV2;

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

public $data;
private $config, $amount;
public $money;

public function onEnable(){
if(!file_exists($this->getDataFolder() . "DiemMy")){
@mkdir($this->getDataFolder() . "DiemMy");
}
$this->quest = new Config($this->getDataFolder() . "DiemMy" . "diemy.yml", Config::YAML);
$this->saveDefaultConfig();
$this->config = $this->getConfig();
$this->config->save();
$this->money = EconomyAPI::getInstance();
$this->kakashi = $this->getServer()->getPluginManager()->getPlugin("NV_Mitsuhi_Otake");
$this->nv = $this->getServer()->getPluginManager()->getPlugin("NGVS_Quest");
$this->getServer()->getPluginManager()->registerEvents($this,$this);
}
public function onJoin(PlayerJoinEvent $event){
if(!($this->quest->exists(strtolower($event->getPlayer()->getName())))){
$this->quest->set(strtolower($event->getPlayer()->getName()), ["1" => "Not", "2" => "Not", "3" => "Not", "4" => "Not", "5" => "Not", "6" => "Not", "7" => "Not", "8" => "Not"]);
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
public function get4(Player $player){
$get4 = $this->quest->get(strtolower($player->getName()))["4"];
return $get4;
}
public function get5(Player $player){
$get5 = $this->quest->get(strtolower($player->getName()))["5"];
return $get5;
}
public function get6(Player $player){
$get6 = $this->quest->get(strtolower($player->getName()))["6"];
return $get6;
}
public function get7(Player $player){
$get7 = $this->quest->get(strtolower($player->getName()))["7"];
return $get7;
}
public function get8(Player $player){
$get8 = $this->quest->get(strtolower($player->getName()))["8"];
return $get8;
}
public function setDone1(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Not", "3" => "Not", "4" => "Not", "5" => "Not", "6" => "Not", "7" => "Not", "8" => "Not"]);
$this->quest->save();
}
public function setDone2(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Not", "4" => "Not", "5" => "Not", "6" => "Not", "7" => "Not", "8" => "Not"]);
$this->quest->save();
}
public function setDone3(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done", "4" => "Not", "5" => "Not", "6" => "Not", "7" => "Not", "8" => "Not"]);
$this->quest->save();
}
public function setDone4(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done", "4" => "Done", "5" => "Not", "6" => "Not", "7" => "Not", "8" => "Not"]);
$this->quest->save();
}
public function setDone5(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done", "4" => "Done", "5" => "Done", "6" => "Not", "7" => "Not", "8" => "Not"]);
$this->quest->save();
}
public function setDone6(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done", "4" => "Done", "5" => "Done", "6" => "Done", "7" => "Not", "8" => "Not"]);
$this->quest->save();
}
public function setDone7(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done", "4" => "Done", "5" => "Done", "6" => "Done", "7" => "Done", "8" => "Not"]);
$this->quest->save();
}
public function setDone8(Player $player){
$this->quest->set(strtolower($player->getName()), ["1" => "Done", "2" => "Done", "3" => "Done", "4" => "Done", "5" => "Done", "6" => "Done", "7" => "Done", "8" => "Done"]);
$this->quest->save();
}
public function onTap(SlapperHitEvent $ev){
$entity = $ev->getEntity();
$damager = $ev->getDamager();
if(!$entity instanceof SlapperHuman && $entity->getName() !== "§l§e【§c!§e】§r§f Shigeo Toku"){
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get1($damager) == "Not"){
$this->UIquest1($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get1($damager) == "Done" && $this->get2($damager) == "Not"){
$this->UIquest2($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get2($damager) == "Done" && $this->get3($damager) == "Not"){
$this->UIquest3($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get3($damager) == "Done" && $this->get4($damager) == "Not"){
$this->UIquest4($damager, "§fĐâu là máu chủ SkyBlock hay nhất?");
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get4($damager) == "Done" && $this->get5($damager) == "Not"){
$this->UIquest5($damager, "§fSkyBlock Co-Op\n§7Có Phải Là Thể Loại Pay To Win Hay Không? Hay Cày Chay Vẫn Mạnh?");
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§fShigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get5($damager) == "Done" && $this->get6($damager) == "Not"){
$this->UIquest6($damager, "§f Đâu Là Máy Chủ Đông Member Nhất Unikfamily?");
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get6($damager) == "Done" && $this->get7($damager) == "Not"){
$this->UIquest7($damager, "§fĐâu Là Tên của server Tổng?");
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get7($damager) == "Done" && $this->get8($damager) == "Not"){
$this->UIquest8($damager);
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Done" && $this->get7($damager) == "Done" && $this->get8($damager) == "Done"){
$this->UIthongbao($damager, "§f•§a đã hoàn thành nhiệm vụ của NPC này!");
return true;
}
if($entity instanceof SlapperHuman && $entity->getName() == "§l§e【§c!§e】§r§f Shigeo Toku" && $this->kakashi->get2($damager) == "Not" && $this->get1($damager) == "Not"){
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
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
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
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§fXin Chào ".$player->getName().", Bạn đã sẵn sàng cho thử thách chưa?");
$form->addButton("§l§1Rồi");
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
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§f Nếu bạn vượt qua các câu hỏi sau bạn sẽ được cấp phép nhiệm vụ mới");
$form->addButton("§l§1Tiếp Tục");
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
$this->UIquest4($player, "§fShigeo Toku");
$this->setDone3($player);
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§fBắt đầu thử thách luôn nhé!");
$form->addButton("§l§1Thực Hiện Thử Thách");
$form->sendToPlayer($player);
return $form;
}
public function UIquest4($player, $msg){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->UIquest5($player, "§bSkyBlock Co-Op");
$this->setDone4($player);
break;
case 1:
$this->UIquest4($player, "§3SkyBlock");
break;
case 2:
$this->UIquest4($player, "§cSkyBlock");
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§r§f Đâu Là Máy Chủ SkyBlock Hay Nhất!");
$form->addButton("§l§eSkyBlock §bCo-Op");
$form->addButton("§l§3SkyBlock");
$form->addButton("§l§cSurvival");
$form->sendToPlayer($player);
return $form;
}
public function UIquest5($player, $msg){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->UIquest6($player, "§fĐâu Là Tên Của Server Tổng?");
$this->setDone5($player);
break;
case 1:
$this->UIquest5($player, "§fSkyBlock Co-Op");
break;
case 2:
$this->UIquest5($player, "§fSkyBlock Co-Op");
break;
case 3:
$this->UIquest5($player, "§fSkyBlock Co-Op");
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent($msg);
$form->addButton("§l§eUnikFamily");
$form->addButton("§l§cVictory");
$form->addButton("§l§3SkyBlock");
$form->addButton("§l§eSkyBlock §bCo-Op");
$form->sendToPlayer($player);
return $form;
}
public function UIquest6($player, $msg){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->UIquest6($player, "§fĐâu Là Tên Của Server Tổng?");
break;
case 1:
$this->UIquest7($player, "§fSkyBlock Co-Op\n§7Có Phải Là Thể Loại: Đua Top Không Pay To Win, Cày Chay Vẫn Mạnh?");
$this->setDone6($player);
break;
case 2:
$this->UIquest6($player, "§fĐâu Là Tên Của Server Tổng?");
break;
case 3:
$this->UIquest6($player, "§fĐâu Là Tên Của Server Tổng?");
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§fThể Loại Được Dùng Trong SkyBlock Co-Op Là Thể Loại Gì?");
$form->addButton("§l§cPay To Win");
$form->addButton("§l§cƯu Tiên Member");
$form->addButton("§l§cGiống SkyFree");
$form->addButton("§l§cBỏ Lượt");
$form->sendToPlayer($player);
return $form;
}
public function UIquest7($player, $msg){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->UIquest8($player);
$this->setDone7($player);
break;
case 1:
$this->UIquest7($player, "§fĐâu Là Máy Chủ Đông Member nhất Unikfamily");
$this->setDone6($player);
break;
case 2:
$this->UIquest7($player, "§f§fThể Loại Được Dùng Trong SkyBlock Co-Op Là Thể Loại Gì?");
break;
case 3:
$this->UIquest7($player, "§fThể Loại Được Dùng Trong SkyBlock Co-Op Là Thể Loại Gì?");
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§fĐâu Là Máy Chủ Đông Member nhất Unikfamily");
$form->addButton("§l§1SkyBlock");
$form->addButton("§l§1Survival");
$form->addButton("§l§1SkyBlock Co-Op");
$form->addButton("§l§1RPG");
$form->sendToPlayer($player);
return $form;
}
public function UIquest8($player){
$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
$form = $api->createSimpleForm(function (Player $player, int $data = null){
$result = $data;
if($result === null){
return true;
}
switch($result){
case 0:
$this->setDone8($player);
$player->addTitle("§l§a【§6✓§a】§r§cĐã Hoàn Thành Xong Thử Thách");
$player->sendMessage("§r§7Quay Lại nói chuyện với Otake");
$this->money->addMoney($player->getName(), 1000);
$dc = Item::get(Item::EMERALD, 0, 32);
$player->getInventory()->addItem($dc);
$dia = Item::get(Item::DIAMOND, 0, 32);
$player->getInventory()->addItem($dia);
$breas = Item::get(Item::BREAD, 0, 16);
$player->getInventory()->addItem($breas);
$this->nv->setNV($player, "Giúp Đỡ", "Mitsuhi Otake", "Nói Chuyện");
break;
}
});
$form->setTitle("§l§e【§c!§e】§r§fShigeo Toku");
$form->setContent("§fKhông hổ danh là Người chơi suất sắc của Máy chủ! Được rồi, ta sẽ cho người 1 ít đồ để dự phòng. Hãy tìm đến NPC nhiệm vụ tiếp theo đề làm nhé!\n§ePhần Thưởng:\n§f•§c 1.000§a Xu\n§f•§c 32 Emerald\n§f•§c 32 Diamond\n§f•§c 16 Bánh Mì\n\n");
$form->addButton("§l§6HOÀN THÀNH");
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