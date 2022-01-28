<?php

namespace DH;

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
use NGVS\NGVS_Mana\Mana\ManaTask;
use pocketmine\network\protocol\SetTitlePacket;

class Main extends PluginBase implements Listener{

public $data;
private $config, $amount;

public function onEnable(){
if(!file_exists($this->getDataFolder() . "MoDau")){
@mkdir($this->getDataFolder() . "MoDau");
}
$this->quest = new Config($this->getDataFolder() . "MoDau" . "data.yml", Config::YAML);
$this->saveDefaultConfig();
$this->config = $this->getConfig();
$this->config->save();
$this->getServer()->getPluginManager()->registerEvents($this,$this);
}
public function onJoin(PlayerJoinEvent $ev){
$player = $ev->getPlayer()->getName();
if(!($this->quest->exists(strtolower($player)))){
$this->quest->set(strtolower($player), ["Tên Nhiệm Vụ" => "Giúp Đỡ", "Hoàn Thành Thử Thách" => "Mitsuhi Otake", "Vị Trí" => "19, 4, 236"]);
$this->quest->save();
return true;
}
}
public function getNhiemVu(Player $player){
$getNhiemVu = $this->quest->get(strtolower($player->getName()))["Tên Nhiệm Vụ"];
return $getNhiemVu;
}
public function getCanGap(Player $player){
$getCanGap = $this->quest->get(strtolower($player->getName()))["Hoàn Thành Thử Thách"];
return $getCanGap;
}
public function getViTri(Player $player){
$getViTri = $this->quest->get(strtolower($player->getName()))["Vị Trí"];
return $getViTri;
}
public function setNV(Player $player, $tennhiemvu, $cangap, $toado){
$this->quest->set(strtolower($player->getName()), ["Tên Nhiệm Vụ" => $tennhiemvu, "Hoàn Thành Thử Thách" => $cangap, "Vị Trí" => $toado]);
$this->quest->save();
return true;
}
public function setNhiemVu(Player $player, $ten){
$getNV = $this->getNhiemVu($player);
$getCG = $this->getCanGap($player);
$getVT = $this->getViTri($player);
$this->quest->set(strtolower($player->getName()), ["Tên Nhiệm Vụ" => $ten, "Hoàn Thành Thử Thách" => $getCG, "Vị Trí" => $getVT]);
$this->quest->save();
return true;
}
public function setCanGap(Player $player, $ten){
$getNV = $this->getNhiemVu($player);
$getCG = $this->getCanGap($player);
$getVT = $this->getViTri($player);
$this->quest->set(strtolower($player->getName()), ["Tên Nhiệm Vụ" => $getNV, "Hoàn Thành Thử Thách" => $ten, "Vị Trí" => $getVT]);
$this->quest->save();
return true;
}
public function setViTri(Player $player, $x, $y, $z){
$getNV = $this->getNhiemVu($player);
$getCG = $this->getCanGap($player);
$getVT = $this->getViTri($player);
$vitri = [$x, $y, $z];
$this->quest->set(strtolower($player->getName()), ["Tên Nhiệm Vụ" => $getNC, "Hoàn Thành Thử Thách" => $getCG, "Vị Trí" => $vitri]);
$this->quest->save();
return true;
}
}