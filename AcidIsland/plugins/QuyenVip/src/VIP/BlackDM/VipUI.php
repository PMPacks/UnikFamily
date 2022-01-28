<?php
declare(strict_types=1);
namespace VIP\BlackDM;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use jojoe77777\FormAPI;
class VipUI extends PluginBase implements Listener{

public function onLoad(){
$this->getLogger()->info("\n§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬\n§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬\n§b❁❁❁❁❁❁❁❁❁❁❁❁❁❁❁❁\n §l§dQuyenVipUI Code By Night§r\n§b❁❁❁❁❁❁❁❁❁❁❁❁❁❁❁❁\n§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬\n§a▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬");
}

public function onEnable(){
$this->getLogger()->info("§a VipUI Enable");
$this->getServer()->getPluginManager()->registerEvents($this, $this);
$this->getServer()->getPluginManager()->getPlugin("FormAPI");
}
  
public function onDisable(){
$this->getLogger()->info(" VipUI Disable");
}
  
public function onCommand(CommandSender $s, Command $cmd, string $label, array $args):bool {
if ($cmd->getName() == "quyenrank"){
    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
   $form = $api->createCustomForm(function (Player $s, $data){

 });
  $form->setTitle("§l§6♦§b Quyền Rank §6♦");
  $form->addLabel("§l§b♦§e Quyền Lợi §dVip§6I:");
  $form->addLabel("§l§a●§b Fly [/fly]");
  $form->addLabel("§l§a●§b Teleport [/tp]");
  $form->addLabel("§l§a●§c Nhận Được Kit §dVip§6I");
  $form->addLabel("§l§b♦§e Quyền Lợi §dVip§6II:");
  $form->addLabel("§l§a●§b Fly [/fly]");
  $form->addLabel("§l§a●§b Teleport [/tp]");
  $form->addLabel("§l§a●§b Feed [/feed]");
  $form->addLabel("§l§a●§c Nhận Được Kit §dVip§6II");
  $form->addLabel("§l§b♦§e Quyền Lợi §dVip§6III:");
  $form->addLabel("§l§a●§b Fly [/fly]");
  $form->addLabel("§l§a●§b Teleport [/tp]");
  $form->addLabel("§l§a●§b Feed [/feed]");
  $form->addLabel("§l§a●§b Heal [/heal]");
  $form->addLabel("§l§a●§c Nhận Được Kit §dVip§6III");
  $form->addLabel("§l§b♦§e Quyền Lợi §dVip§6IV:");
  $form->addLabel("§l§a●§b Fly [/fly]");
  $form->addLabel("§l§a●§b Teleport [/tp]");
  $form->addLabel("§l§a●§b Feed [/feed]");
  $form->addLabel("§l§a●§b Heal [/heal]");
  $form->addLabel("§l§a●§b Size [/size]");
  $form->addLabel("§l§a●§c Nhận Được Kit §dVip§6IV");
  $form->addLabel("§l§a●§c Mở Được 2 Kho Đồ [/khodo]");
  $form->addLabel("§l§b♦§e Quyền Lợi §aVipV:");
  $form->addLabel("§l§a●§b Fly [/fly]");
  $form->addLabel("§l§a●§b Teleport [/tp]");
  $form->addLabel("§l§a●§b Feed [/feed]");
  $form->addLabel("§l§a●§b Heal [/heal]");
  $form->addLabel("§l§a●§b Size [/size]");
  $form->addLabel("§l§a●§b Nick [/nick]");
  $form->addLabel("§l§a●§c Nhận Được Kit §aRich§cKid");
  $form->addLabel("§l§a●§c Mở Được 3 Kho Đồ [/khodo]");
  $form->sendToPlayer($s); 
}
return true;
}
}