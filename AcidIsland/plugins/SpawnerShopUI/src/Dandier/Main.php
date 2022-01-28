<?php

namespace Dandier;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\item\Item;

use pocketmine\level\sound\PopSound;


use onebone\pointapi\PointAPI;

use Dandier\Sound\SoundSuccess;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;
class Main extends PluginBase implements Listener{
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("PointAPI");
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        switch($cmd->getName()){                    
            case "mualong":
                if($sender instanceof Player){
                    $this->MainForm($sender);
                        return true;
                    }
            break;
        }
        return true;
    }
    
	public function MainForm(Player $s){
        $f = new SimpleForm(function (Player $s, $data){
            if(!isset($data)){
                return false;
            }
            switch ($data){
                case 0:
                $f1 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 45){
                    $this->eco->reducePoint($s, 45); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("cow", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f1->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f1->setContent("§l§c▶§r§7 Bạn cố muốn mua §fcow spawner §7với 45 Points Không ?");
                $f1->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f1->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f1);
                break;
                case 1:
                $f2 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 100){
                    $this->eco->reducePoint($s, 100); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("husk", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f2->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f2->setContent("§l§c▶§r§7 Bạn cố muốn mua §fhusk spawner §7với 100 Points Không ?");
                $f2->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f2->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f2);
                break;
                case 2:
                $f3 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 45){
                    $this->eco->reducePoint($s, 45); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("sheep", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f3->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f3->setContent("§l§c▶§r§7 Bạn cố muốn mua §fsheep spawner §7với 45 Points Không ?");
                $f3->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f3->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f3);
                break;
                case 3:
                $f4 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 45){
                    $this->eco->reducePoint($s, 45); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("panda", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f4->setTitle("§6§l♦ §d§6§l♦ §dMua Lồng §6§l♦ §6§l♦");
                $f4->setContent("§l§c▶§r§7 Bạn cố muốn mua §fpanda spawner §7với 45 Points Không ?");
                $f4->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f4->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f4);
                break;
                case 4:
                $f5 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 100){
                    $this->eco->reducePoint($s, 100); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("zombie", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f5->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f5->setContent("§l§c▶§r§7 Bạn cố muốn mua §fzombie spawner §7với 100 Points Không ?");
                $f5->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f5->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f5);
                break;
                case 5:
                $f6 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 155){
                    $this->eco->reducePoint($s, 155); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("vindicator", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f6->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f6->setContent("§l§c▶§r§7 Bạn cố muốn mua §7vindicator spawner §7với 155 Points Không ?");
                $f6->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f6->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f6);
                break;
                case 6:
                $f7 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 45){
                    $this->eco->reducePoint($s, 45); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("mooshroom", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f7->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f7->setContent("§l§c▶§r§7 Bạn cố muốn mua §fmooshroom spawner §7với 45 Points Không ?");
                $f7->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f7->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f7);
                break;
                case 7:
                $f8 = new ModalForm(function (Player $s, $data){
                    if(!isset($data)){
                        return $this->MainForm($s);
                    }
                    switch($data){
                        case 1:
                    $point = $this->eco->myPoint($s);
                    if($point >= 45){
                    $this->eco->reducePoint($s, 45); 
                    $s->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("polarbear", "1"));
                    $s->sendMessage($this->getConfig()->get("succes"));
                }else{
                $s->sendMessage($this->getConfig()->get("failed"));
                }
                        break;
                case 2:
                break;
                    }
                });
                $f8->setTitle("§6§l♦ §dMua Lồng §6§l♦");
                $f8->setContent("§l§c▶§r§7 Bạn cố muốn mua §fpolarbear spawner §7với 45 Points Không ?");
                $f8->setButton1("§l§3●§2 Đồng Ý! §3●");
                $f8->setButton2("§l§3●§2 Thôi Để Sau! §3●");
                $s->sendForm($f8);
                break;
                case 8:
                break;
            }
        });
        $f->setTitle("§6§l♦ §dMua Lồng §6§l♦");
        $f->setContent("§l§c▶§r§7 Lựa chọn lồng mà bạn muốn mua:");
        $f->addButton("§l§3●§2 Cow Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Husk Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Sheep Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Panda Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Zombie Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Vindicator Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Mooshroom Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 PolarBear Spawner §3●", 1, "https://resimkutuphanesi.play.tc/library/gallery/PreresizedIconPackage/MobsEggs/Spawner/Zombie-spawner.png");
        $f->addButton("§l§3●§2 Thoát §3●");
        $s->sendForm($f);
        return $f;
    }
	
	/**public function openSpawner2(Player $sender, Item $item){
        $this->eco = $this->getServer()->getPluginManager()->getPlugin("PointAPI");
		$hand = $sender->getInventory()->getItemInHand()->getCustomName();
        $inventory = $this->spawner->getInventory();
        if($item->getId() == 160){
            $sender->sendMessage("§aCảm ơn đã sử dụng §6§l♦ §dMua Lồng §6§l♦" /** sorry plugin ini belum terlalu stabil, karena saya belum terbiasa di plugin gui);
        /**}
        if($item->getId() == 35 && $item->getDamage() == 0){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 45){
			    $this->eco->reducePoint($sender, "45"); 
			    $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("cow", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
			}
        }      
        if($item->getId() == 35 && $item->getDamage() == 1){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 100){
			    $this->eco->reducePoint($sender, "100"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("husk", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
            }
        }
        if($item->getId() == 35 && $item->getDamage() == 2){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 45){
			    $this->eco->reducePoint($sender, "45"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("sheep", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
            }
        }
        if($item->getId() == 35 && $item->getDamage() == 3){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 45){
			    $this->eco->reducePoint($sender, "45"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("panda", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
            }
        }
        if($item->getId() == 35 && $item->getDamage() == 4){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 100){
			    $this->eco->reducePoint($sender, "100"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("zombie", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
            }
        }
        if($item->getId() == 35 && $item->getDamage() == 5){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 155){
			    $this->eco->reducePoint($sender, "155"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("vindicator", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
            }
        }
        if($item->getId() == 35 && $item->getDamage() == 6){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 45){
			    $this->eco->reducePoint($sender, "45"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("mooshroom", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));
            }
        }
        if($item->getId() == 35 && $item->getDamage() == 7){
	        $point = $this->eco->myPoint($sender);
	        if($point >= 45){
			    $this->eco->reducePoint($sender, "45"); 
                $sender->getInventory()->addItem(\Heisenburger69\BurgerSpawners\Main::getInstance()->getSpawner("polarbear", "1"));
                $sender->sendMessage($this->getConfig()->get("succes"));
            }else{
                $sender->sendMessage($this->getConfig()->get("failed"));      
               
            }
		 }
      }**/
  }