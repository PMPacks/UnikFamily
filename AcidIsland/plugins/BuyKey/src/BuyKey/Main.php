<?php
#Code Bởi PIG
namespace BuyKey;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command,CommandSender, CommandExecutor, ConsoleCommandSender};
use pocketmine\event\Listener;
use jojoe77777\FormAPI;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;

class Main extends PluginBase implements Listener {
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool
    {
        switch($cmd->getName()){
        case "buykey":
        if(!($sender instanceof Player)){
        $sender->sendMessage("§cDont use here");
		return true;
		}
		$this->sendMainForm($sender);
		return true;
		}
	}
	public function sendMainForm($sender){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
            	    case 0:
					break;
                    case 1:
                    $this->MyThic($sender);
                    break;
                    case 2:
                    $this->Common($sender);
                    break;
                    case 3:
                    $this->Lucky($sender);
					
			}       
        });
     $money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($sender);
      $form->setTitle("§l§e•§a BUY Key §e•");
      $form->setContent("§l§e•§a Xin chào: §c".$sender->getName()."\n§l§e•§a Your Money:§b ".$money."");
	  $form->addButton("§l§f•§c Thoát§f •",0,"textures/other/axit");
      $form->addButton("§l§f•§9 Armor§c ↣§6 1.000.000 §eXu§f •",0,"textures/ui/accessibility_glyph_color");
      $form->addButton("§l§f•§9 Daily§c ↣§6 100.000 §eXu§f •",0,"textures/ui/accessibility_glyph_color");
      $form->addButton("§l§f•§9 Legendary§c ↣§6 150.000 §eXu§f •",0,"textures/ui/accessibility_glyph_color");
      $form->sendToPlayer($sender);
	}
    public function Mythic($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
			case 0:
			break;
            case 1:
            $p = $sender->getName();
            $money = $this->money->myMoney($sender);
            if($money >= 1000000){
            $this->money->reduceMoney($sender, 1000000);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "pckey Armor 1 ".$sender->getName());
            return true;
            }else{
            $sender->sendMessage("§l§cBạn không đủ §e$§c để mua Key Armor");
            }
            }
        });
        $form->setTitle("§l§e•§a Armor §e•");
		$form->setContent("§l§e↣§c Key:§l§a Armor§l§e Giá:§c 50.000§dXu§b Bạn có đồng ý mua không?");
        $form->addButton("§l§e•§c Không §e•",0,"textures/ui/cancel");
        $form->addButton("§l§e•§a Đồng Ý §e•",0,"textures/ui/confirm");
        $form->sendToPlayer($sender);
    }
	public function Common($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
			case 0:
			break;
            case 1:
            $p = $sender->getName();
            $money = $this->money->myMoney($sender);
            if($money >= 100000){
            $this->money->reduceMoney($sender, 100000);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "key Daily 1 ".$sender->getName());
            return true;
            }else{
            $sender->sendMessage("§l§cBạn không đủ §e$§c để mua Key VTL");
            }
            }
        });
        $form->setTitle("§l§e•§a Daily §e•");
		$form->setContent("§l§e↣§c Key:§l§a Daily§l§e Giá:§c 100.000§dXu§b Bạn có đồng ý mua không?");
        $form->addButton("§l§e•§c Không §e•",0,"textures/ui/cancel");
        $form->addButton("§l§e•§a Đồng Ý §e•",0,"textures/ui/confirm");
        $form->sendToPlayer($sender);
	}
	 public function Lucky($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
			case 0:
			break;
            case 1:
            $p = $sender->getName();
            $money = $this->money->myMoney($sender);
            if($money >= 150000){
            $this->money->reduceMoney($sender, 150000);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "pckey Legendary 1 ".$sender->getName());
            return true;
            }else{
            $sender->sendMessage("§l§cBạn không đủ §e$§c để mua Key Lucky");
            }
            }
        });
        $form->setTitle("§l§e•§a Legendary §e•");
		$form->setContent("§l§e↣§c Key:§l§a Legendary§l§e Giá:§c 150.000§dXu§b Bạn có đồng ý mua không?");
        $form->addButton("§l§e•§c Không §e•",0,"textures/ui/cancel");
        $form->addButton("§l§e•§a Đồng Ý §e•",0,"textures/ui/confirm");
        $form->sendToPlayer($sender);
    }
	public function nomoneyUI($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $sender, $data){
            $result = $data;
            if ($result == null) {
            }
            switch ($result) {
			case 0:
			$this->sendMainForm($sender);
			break;
			case 1:
			break;
			}
		});
		$form->setTitle("§l§cError");
		$form->setContent("§l§cBạn không đủ tiền để mua key!");
		$form->addButton("§l§e•§a Quay Lại §e•", 0);
		$form->addButton("§l§e•§c Thoát §e•", 1);
	}
}
			
		