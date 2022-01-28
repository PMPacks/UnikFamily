<?php

namespace ShinPickaxeLevel;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\event\player\{PlayerItemHeldEvent, PlayerJoinEvent, PlayerQuitEvent};
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

use pocketmine\item\Item;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

use DaPigGuy\PiggyCustomEnchants\Main as CE;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\entity\EntityRegainHealthEvent;
use ShinPickaxeLevel\provider\DataProvider;
use ShinPickaxeLevel\task\SortTask;
use ShinPickaxeLevel\task\SortTask2;
use jojoe77777\FormAPI\CustomForm;
class Main extends PluginBase implements Listener{
 
	public $data,$givecup;
	private static $instance;
	public function onEnable(){
		if(!is_file($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		$this->DataProvider = new DataProvider($this);
		$this->level = new Config($this->getDataFolder() . "level.yml", Config::YAML);
		$this->rb = new Config($this->getDataFolder() . "rb.yml", Config::YAML);
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getLogger()->info("§c➡➡➡➡➡➡➡➡➡➡➡➡➡\n\n§l§f•§b ShinPickaxeLevel By Shin1134 Enable\n\n§c➡➡➡➡➡➡➡➡➡➡➡➡➡");
		$this->eco =  $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->CE =  $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        $this->point =  $this->getServer()->getPluginManager()->getPlugin("PointAPI");
		$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		self::$instance = $this;

		//$this->old = new Config($this->getDataFolder() . "user.yml", Config::YAML);
		/*foreach($this->old->getAll() as $key => $val){//
			

			$name = trim(strtolower($key));
			@mkdir($this->getDataFolder() . "players/" . $name{0} . "/");
			$data = new Config($this->getDataFolder() . "players/" . $name{0} . "/$name.yml", Config::YAML);
			$data->set("level", $val["Level"]);
			$data->set("exp", $val["exp"]);
			$data->set("nextexp", $val["nextexp"]);
			$data->save();
		}*///

	}
	
	public function onDisable(){
		foreach($this->getserver()->getOnlinePlayers() as $player){
			if($this->getDataProvider()->getPlayer($player) == null){
				$this->getDataProvider()->registerPlayer($player);
			}
			$this->getDataProvider()->savePlayer($player, $this->data[$player->getName()]);
		}		
	}	
	
	public static function getInstance(){
		return self::$instance;
	}
	
	public function getDataProvider(){
		return $this->DataProvider;
	}
	
	public function onJoin(PlayerJoinEvent $ev){
		if($this->getDataProvider()->getPlayer($ev->getPlayer()) == null){
			$this->getDataProvider()->registerPlayer($ev->getPlayer());
		}
		$this->data[$ev->getPlayer()->getName()] = $this->getDataProvider()->getPlayer($ev->getPlayer());
		$this->upHeal($ev->getPlayer(), $this->getHeal($ev->getPlayer()));
		if(!$this->level->exists(strtolower($ev->getPlayer()->getName()))){
			$this->level->set(strtolower($ev->getPlayer()->getName()), 1);
			$this->level->save();
		}
		if(!$this->rb->exists(strtolower($ev->getPlayer()->getName()))){
			$this->rb->set(strtolower($ev->getPlayer()->getName()), 1);
			$this->rb->save();
		}
	}
	
	public function onQuit(PlayerQuitEvent $ev){
		if($this->getDataProvider()->getPlayer($ev->getPlayer()) == null){
			$this->getDataProvider()->registerPlayer($ev->getPlayer());
		}
		$this->getDataProvider()->savePlayer($ev->getPlayer(), $this->data[$ev->getPlayer()->getName()]);
	}	
	
	public function onItemHeld(PlayerItemHeldEvent $ev){
		
		$p = $ev->getPlayer();
		$level = $this->getLevel($p);
		$exp = $this->getExp($p);
		$next = $this->getNextExp($p);
		$pickaxename = $this->getNamePickaxe($p);
		$i = $p->getInventory()->getItemInHand();
		$hand = $i->getCustomName();
		$it = explode(" ", $hand);
		$damage = $i->getDamage();	
		if ($it[0] == "§l§e⚡") {
			$p->sendPopup("   §l§e⚡§6 ᴘɪᴄᴋᴀxᴇ §7│ §bιsʟᴀɴᴅ§c §e⚡\n" . "§e♜§b Kinh Nghiệm:§a " . $exp ."§l§6 /§c ".$next. "§7 |§6 Cấp Cúp: §a" . $level);
			if ($damage > 780) {
				$i->setDamage(0);
				$p->getInventory()->setItemInHand($i);
				$p->sendMessage("§l§3●§e Cúp đã được sửa chữa miễn phí!");
			}
		}	
			
		if(isset($this->need[strtolower($p->getName())]) && $i->getId() == 278){
			$this->getLogger()->info($pickaxename ."\n");
			$i->setCustomName($pickaxename);
			
			if($this->getLevel($p) % 2 == 0){
                $id = 15;
                $lv = $this->getLevel($p)/2.5;
				$i->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($id), $lv));
			}elseif(($this->getLevel($p) % 2 !== 0) && ($this->getLevel($p) >=50)){
                    $id = 18;
                    $lv = $this->getLevel($p)/3;
				$i->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($id), $lv));
				
			}
			$p->getInventory()->setItemInHand($i);
			switch($this->getLevel($p)){
				case 100:
					$this->addCE(new ConsoleCommandSender(), "Energizing", 1, $p->getName());
				break;
				case 200:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 1, $p->getName());
				break;
				case 300:
					$this->addCE(new ConsoleCommandSender(), "Energizing", 2, $p->getName());
				break;
                case 400:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 2, $p->getName());
				break;	
                case 500:
					$this->addCE(new ConsoleCommandSender(), "Haste", 1, $p->getName());
				break;	
                case 600:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 3, $p->getName());
				break;
                case 700:
					$this->addCE(new ConsoleCommandSender(), "Haste", 2, $p->getName());
				break;	
                case 800:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 4, $p->getName());
				break;		
                case 900:
					$this->addCE(new ConsoleCommandSender(), "Haste", 3, $p->getName());
				break;
                case 1000:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 5, $p->getName());
				break;			
			}							
			unset($this->need[strtolower($p->getName())]);
		}	
		if(isset($this->givecup[strtolower($p->getName())]) && $i->getId() == 278){
			if($this->getLevel($p) >=100){
				for($a=100;$a<=$this->getLevel($p);$a++){

				switch($a){
				case 100:
					$this->addCE(new ConsoleCommandSender(), "Energizing", 1, $p->getName());
				break;
				case 200:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 1, $p->getName());
				break;
				case 300:
					$this->addCE(new ConsoleCommandSender(), "Energizing", 2, $p->getName());
				break;
                case 400:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 2, $p->getName());
				break;	
                case 500:
					$this->addCE(new ConsoleCommandSender(), "Haste", 1, $p->getName());
				break;	
                case 600:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 3, $p->getName());
				break;
                case 700:
					$this->addCE(new ConsoleCommandSender(), "Haste", 2, $p->getName());
				break;	
                case 800:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 4, $p->getName());
				break;		
                case 900:
					$this->addCE(new ConsoleCommandSender(), "Haste", 3, $p->getName());
				break;
                case 1000:
					$this->addCE(new ConsoleCommandSender(), "Jackpot", 5, $p->getName());
				break;			
					}	
				}	
				unset($this->givecup[strtolower($p->getName())]);				
			}		
		}
		
	}
	
	public function onBreak(BlockBreakEvent $ev){
		$p = $ev->getPlayer();
		$name = $p->getName();
		$i = $p->getInventory()->getItemInHand();
		$icn = $i->getCustomName();
		$pas = explode(" ", $icn);
	
		if($pas[0] == "§l§e⚡"){
			if(strpos($icn, $name)  == false){
				$ev->setCancelled(true);
				$p->sendMessage("§l§3●§e Vật phẩm không phải của bạn!");
				}
		}
					
			foreach($ev->getDrops() as $drop) {
				if($ev->isCancelled()) {
                    return;
				}
				if($ev->getPlayer()->getInventory()->canAddItem($drop)) {
					if($this->getLevel($p) >=500){
						if($ev->getPlayer()->getInventory()->canAddItem($drop)) {
							$ev->getPlayer()->getInventory()->addItem($drop);
						}
					}			

				}
			}			

		    if($pas[0] == "§l§e⚡"){
				if($this->getLevel($p) == 1000){
					return false;
				}			
                if($ev->isCancelled()) {
                    return;
				} 				
				$block = $ev->getBlock();
				$level = $this->getLevel($p);
		         $exp = $this->getExp($p);
				$next = $this->getNextExp($p);
				$p->sendPopup("   §l§e⚡§6 ᴘɪᴄᴋᴀxᴇ §7│ §bιsʟᴀɴᴅ§c §e⚡\n" . "§e♜§b Kinh Nghiệm:§a " . $exp ."§l§6 /§c ".$next. "§7 |§6 Cấp Cúp: §a" . $level);
				switch($block->getId()) {
					case 56:
                       $this->addExp($p, 3);
                       break;
					case 14:
                       $this->addExp($p, 2);
                       break;
					case 15:
                       $this->addExp($p, 2);
                       break;
					case 16:
                       $this->addExp($p, 2);
                       break;
					case 129:
                       $this->addExp($p, 3);
                       break;
					case 133:
                       $this->addExp($p, 4);
                       break;
					case 57:
                        $this->addExp($p, 4);
						break;
					case 42:
                       $this->addExp($p, 3);
					   break;
					case 1:
                       $this->addExp($p, 1);
					   break;
					case 41:
                       $this->addExp($p, 3);
                       break;
					case 73:
                       $this->addExp($p, 2);
                       break;
					case 173:
                       $this->addExp($p, 3);
                       break;
					case 74:
                       $this->addExp($p, 2);
                       break;
					case 153:
                       $this->addExp($p, 2);
                       break;
					default:
						return false;
				}	

				if($this->getExp($p) >= $this->getNextExp($p)){
					$this->levelUp($p);
					$p->addTitle("§l§aCúp Cấp: §e".$this->getLevel($p));
					$money = $this->getLevel($p) * 1000;
					if(in_array($this->getLevel($p), [1000 ])){
					    $point = $this->getLevel($p)/2;
                        $this->Point->addMoney($p->getName(), $point);
						$p->sendMessage("§l§c●§a Bạn được cộng§b $point coin");
                    }
					$this->eco->addMoney($p->getName(), $money);
					$p->sendMessage("§l§c●§a Bạn được cộng§e $money xu");
					$p->sendMessage("§l§c●§6 Cúp Của Bạn Đã Được Nâng Cấp");
					$this->getServer()->broadcastMessage("§l§3●§e Cúp của người chơi§l§c ".$p->getName()."§r§6 Vừa lên cấp:§b ".$this->getLevel($p));
	
					$this->need[strtolower($p->getName())] = true;
				}
			}
			
			if($ev->isCancelled()) {
                return;
			}	
			$p = $ev->getPlayer();
			$cs = $this->getRebirth($p);	
			if($cs > 1){
				$ran = mt_rand(1,100);
				$add = 0;				
				switch($ran){
					case 50:
					  $add = 5000*$cs;
					break;
					default:
					break;				
				}
				if($add > 1){
					$this->eco->addMoney($p,$add);
					$p->sendMessage("§l§3●§a Bạn đã nhận được§d $add xu §btừ chuyển sinh cấp§c $cs");
				}
			}
	}
	
	public function onCommand(CommandSender $sender, Command $cmd,string $label, array $args) : bool {
         if($cmd->getName() == "givecup"){
			 if($sender->isOp() || $sender->getName() == "YTBJero"){
                if(!isset($args[0])){
                    $sender->sendMessage("§l§3●§a /givecup <player>");
                    return true;
                }else{
                    $player = $this->getServer()->getPlayer($args[0]);
                    if(!$player == null){
                        if($player->isOnline()) {
                            $inv = $player->getInventory();
                            $cup = Item::get(278, 0, 1);
                            $pickname = $this->getNamePickaxe($player);
                            $cup->setCustomName($pickname);
                            $lvn = $this->getLevel($player);
							for($a=($lvn -1) ;$a<=$lvn;$a++){
	
								if($a % 2 == 0){
									$id = 15;
									$lv = $a/2.5;
									$cup->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($id), $lv));
									$player->sendMessage("§l§c●§e Cúp đã được cường hóa: §cHiệu xuất§e Cấp độ §c".$lv);
									
								}elseif(($a % 2 !== 0) && ($a >=50)){
										$id = 18;
										$lv = $a/3;
										$cup->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($id), $lv));
										$player->sendMessage("§l§c●§e Cúp đã được cường hóa: §cGia tài§e Cấp độ §c".$lv);
								}
							}
                            $inv->addItem($cup);		
							$this->givecup[strtolower($player->getName())] = true;
						}
                    }
                }

             }else{
                 $sender->sendMessage("You don't permission use command");
                 return true;
             }
		}elseif($cmd->getName() == "topcup"){
			 $levelplot = $this->level->getAll();
			$max = 0;
			$max = count($levelplot);
			$max = ceil(($max / 5));
			
			$message = "";
			$message1 = "";
			
			$page = array_shift($args);
			$page = max(1, $page);
			$page = min($max, $page);
			$page = (int)$page;
			
			$aa = $this->level->getAll();
			arsort($aa);
			$i = 0;
			
			foreach ($aa as $b=>$a) {
				if (($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4) {
					$i1 = $i + 1;
					$trang = "§l§c•§e Trang:§a ".$page."§f/§c".$max."\n";
					$message .= "§l§bHạng§e ".$i1." §bthuộc về§c ".$b." §fvới§e ".$a."§a Level\n";
					$message1 .= "§l§bHạng§e ".$i1." §bthuộc về§c ".$b." §fvới§e ".$a."§a Level\n";
				} $i++;
			}
			$form = new CustomForm(function (Player $sender, $data) use ($trang, $message) {
				if ($data === null) { return false; }
				$this->getServer()->dispatchCommand($sender, "topcup " . $data[1]);
			});
			$form->setTitle("§l§e•§6 TOP CÚP §e•");
			$form->addLabel($trang. $message);
			$form->addInput("§l§3•§a Qua Trang:");
			$form->sendToPlayer($sender);
		}elseif($cmd->getName() == "chuyensinh"){
			$tien = $this->eco->myMoney($sender);
			$amount = 20000000;
			$solan = $this->getRebirth($sender);
			if(!($tien >= $amount*$solan)){
				$sender->sendMessage("§l§3●§e Bạn không có đủ tiền để§a chuyển sinh! §eBạn cần§c ".$amount*$solan." xu");
			}else{
				$sender->sendMessage("§l§3●§e Bạn đã §achuyển sinh §ethành công! + 1 lần chuyển sinh");
				$this->eco->reduceMoney($sender, $amount*$solan);
				$this->addRebirth($sender,1);
				$this->upHeal($sender, $sender->getMaxHealth() + 1);
			
				$sender->sendMessage("§l§cMax Health§b( + 1)§c: §e" . $sender->getMaxHealth());
			}			
		}elseif($cmd->getName() == "topchuyensinh"){
			 $levelplot = $this->rb->getAll();
			$max = 0;
			$max = count($levelplot);
			$max = ceil(($max / 5));
			
			$message = "";
			$message1 = "";
			
			$page = array_shift($args);
			$page = max(1, $page);
			$page = min($max, $page);
			$page = (int)$page;
			
			$aa = $this->rb->getAll();
			arsort($aa);
			$i = 0;
			
			foreach ($aa as $b=>$a) {
				if (($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4) {
					$i1 = $i + 1;
					$trang = "§l§c•§e Trang:§a ".$page."§f/§c".$max."\n";
					$message .= "§l§bHạng§e ".$i1." §bthuộc về§c ".$b." §fvới§e ".$a."§a Level\n";
					$message1 .= "§l§bHạng§e ".$i1." §bthuộc về§c ".$b." §fvới§e ".$a."§a Level\n";
				} $i++;
			}
			$form = new CustomForm(function (Player $sender, $data) use ($trang, $message) {
				if ($data === null) { return false; }
				$this->getServer()->dispatchCommand($sender, "topcup " . $data[1]);
			});
			$form->setTitle("§l§e•§6 TOP CHUYỂN SINH §e•");
			$form->addLabel($trang. $message);
			$form->addInput("§l§3•§a Qua Trang:");
			$form->sendToPlayer($sender);
		}

		return true;
	}
	
    public static function sendTop($receiver, $data,  int $page = 1, Form $closeForm = null)
    {
        if ($receiver instanceof Player) $receiver = $receiver->getLowerCaseName();
        $server = Server::getInstance();
        $plugin = Main::getInstance();
        $banned = [];
        foreach ($server->getNameBans()->getEntries() as $entry) {
            if (isset($data[strtolower($entry->getName())])) {
                $banned[] = $entry->getName();
            }
        }
        $ops = [];
        foreach ($server->getOps()->getAll() as $op) {
            if (isset($data[strtolower($op)])) {
                $ops[] = $op;
            }
        }

        $task = new SortTask($receiver, $data, false, $page, $ops, $banned, $closeForm);
        $server->getAsyncPool()->submitTask($task);
    }	

    public static function sendTop2($receiver, $data,  int $page = 1, Form $closeForm = null)
    {
        if ($receiver instanceof Player) $receiver = $receiver->getLowerCaseName();
        $server = Server::getInstance();
        $plugin = Main::getInstance();
        $banned = [];
        foreach ($server->getNameBans()->getEntries() as $entry) {
            if (isset($data[strtolower($entry->getName())])) {
                $banned[] = $entry->getName();
            }
        }
        $ops = [];
        foreach ($server->getOps()->getAll() as $op) {
            if (isset($data[strtolower($op)])) {
                $ops[] = $op;
            }
        }

        $task = new SortTask2($receiver, $data, false, $page, $ops, $banned, $closeForm);
        $server->getAsyncPool()->submitTask($task);
    }
	
	public function getLevel(Player $player){
		if(!isset($this->data[$player->getName()]["level"])){
			if($this->getDataProvider()->getPlayer($ev->getPlayer()) == null){
				$this->getDataProvider()->registerPlayer($ev->getPlayer());
			}
			$this->data[$ev->getPlayer()->getName()] = $this->getDataProvider()->getPlayer($ev->getPlayer());
			$this->upHeal($ev->getPlayer(), $this->getHeal($ev->getPlayer()));			
		}
		return $this->data[$player->getName()]["level"];
	}
	
	public function getExp(Player $player){
		return $this->data[$player->getName()]["exp"];
	}
	
	public function getNextExp(Player $player){
		return $this->data[$player->getName()]["nextexp"];
	}

	public function levelUp(Player $player){
		if($this->getLevel($player) >= 1000){
			$player->sendMessage("§l§l§c●§e Cúp bạn đã đạt cấp tối đa!");
			$this->data[$player->getName()]["level"] = 500;
			$this->data[$player->getName()]["exp"] = 0;
			$this->data[$player->getName()]["nextexp"] = 0;
		}
		$this->data[$player->getName()]["level"] = $this->getLevel($player) + 1;
		$this->data[$player->getName()]["exp"] = 0;
		$this->data[$player->getName()]["nextexp"] = ($this->getLevel($player)+1)*400;
		$this->level->set(strtolower($player->getName()), $this->getLevel($player));
		$this->level->save();		
	}	
	
	public function addExp(Player $player, $exp){
		//var_dump($this->data[$player->getName()]["exp"]);
		$this->data[$player->getName()]["exp"] = $this->getExp($player) + $exp;
	}
	
	public function nextExpUp(Player $player, $nextexp){
		$this->data[$player->getName()]["nextexp"] = $nextexp + 400;
	}	
	
	public function getNamePickaxe(Player $player){
		return "§l§e⚡ §6ιsʟᴀɴᴅ §bᴘɪᴄᴋᴀxᴇ §7[§cLevel:§6 ". (string)$this->getLevel($player) ." §7]§a ".$player->getName(); 

	}	

/*

█▀▀█ █▀▀ █▀▀▄ ░▀░ █▀▀█ ▀▀█▀▀ █░░█   █▀▀█ █▀▀█ ░▀░
█▄▄▀ █▀▀ █▀▀▄ ▀█▀ █▄▄▀ ░░█░░ █▀▀█   █▄▄█ █░░█ ▀█▀
▀░▀▀ ▀▀▀ ▀▀▀░ ▀▀▀ ▀░▀▀ ░░▀░░ ▀░░▀   ▀░░▀ █▀▀▀ ▀▀▀

*/

	public function getRebirth(Player $player){
		return isset($this->data[$player->getName()]["rebirth"]) ? $this->data[$player->getName()]["rebirth"] : false ;
	}

	public function addRebirth(Player $player, $exp){
		$this->data[$player->getName()]["rebirth"] = $this->getRebirth($player) + $exp;
		$this->rb->set(strtolower($player->getName()), $this->getRebirth($player));
		$this->rb->save();		
	}
	
	public function getHeal(Player $player){
		return $this->data[$player->getName()]["health"];	
	}
	
	public function upHeal(Player $player, $heal){
		$add = 20 + $this->getRebirth($player);
		$player->setMaxHealth($add);
		$player->heal(new EntityRegainHealthEvent($player,$heal , EntityRegainHealthEvent::CAUSE_CUSTOM));	
	}


//-------------------------------------------------------------------------------------

	public function addCE(CommandSender $sender, $enchantment, $level, $target)
    {
        $plugin = $this->CE;
        if ($plugin instanceof CE) {
            if (!is_numeric($level)) {
                $level = 1;
                $sender->sendMessage(TextFormat::RED . "Level must be numerical. Setting level to 1.");
            }
            $target == null ? $target = $sender : $target = $this->getServer()->getPlayer($target);
            if (!$target instanceof Player) {
                if ($target instanceof ConsoleCommandSender) {
                    $sender->sendMessage(TextFormat::RED . "Please provide a player.");
                    return;
                }
                $sender->sendMessage(TextFormat::RED . "Invalid player.");
                return;
            }
            $target->getInventory()->setItemInHand($plugin->addEnchantment($target->getInventory()->getItemInHand(), $enchantment, $level, $sender->hasPermission("customenchants.overridecheck") ? false : true, $sender));
		}
	}
}