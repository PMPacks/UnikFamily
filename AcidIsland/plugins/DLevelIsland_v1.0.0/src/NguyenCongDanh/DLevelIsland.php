<?php 

namespace NguyenCongDanh;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\block\Block;
use pocketmine\item\Item;

use pocketmine\utils\{Config, TextFormat};

Class DLevelIsland extends PluginBase implements Listener{
	
	public $prefix = "§l§3●§e ";
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->chat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
		//vui lòng tôn trọng tác giả không đổi author và không xóa 2 dòng này
		$this->getServer()->getLogger()->info($this->prefix. TextFormat::GREEN. "Remade By: " .TextFormat::RED. "Nguyễn Công Danh (NCD)");
		$this->getServer()->getLogger()->info($this->prefix. TextFormat::GREEN. "Facebook: " .TextFormat::RED. "Fb.com/NguyenCongDanh205");
		
		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		
		@mkdir($this->getDataFolder());
		$this->level = new Config($this->getDataFolder() . "Level.yml", Config::YAML);
		$this->exp = new Config($this->getDataFolder() . "EXPLevel.yml", Config::YAML);
	    $this->nextexp = new Config($this->getDataFolder() . "NextEXPLevel.yml", Config::YAML);
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer()->getName();
		if($this->nextexp->get($player) > 0){
		} else {
			$this->level->set($player, 1);
			$this->exp->set($player, 1);
			$this->nextexp->set($player, 100);
			$this->level->save();
			$this->exp->save();
			$this->nextexp->save();
		}
	}
	
	public function onBlock(BlockPlaceEvent $event){
		if ($event->isCancelled()){return;}
		$player = $event->getPlayer()->getName();
		$sender = $event->getPlayer();
		if($this->exp->get($player) < $this->nextexp->get($player)){
			$this->exp->set($player, $this->exp->get($player) +0.5);
		} else {
			$this->level->set($player,$this->level->get($player) +1);
			$this->exp->set($player, 0);
			$this->nextexp->set($player, $this->nextexp->get($player) + 50);
			$this->level->save();
			$this->exp->save();
			$this->nextexp->save();
			
			$money = 1000;
			$this->eco->addMoney($sender, $money);
			
			$this->getServer()->broadcastMessage($this->prefix . "§eLevel island của người chơi §a".$player." §eđã lên cấp §a".$this->level->get($player));
			$sender->sendMessage($this->prefix . "§eLevel island đảo của bạn đã được lên cấp §a".$this->level->get($player));
			$sender->sendMessage($this->prefix . "§eBạn nhận được §a$money xu §ekhi lên level.");
		}
	}
	
	public function onChat(PlayerChatEvent $event){
		$this->chat->setPrefix($this->level->get($event->getPlayer()->getName()), $event->getPlayer());
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		if($cmd->getName() == "levelisland"){
			$this->MenuForm($sender);
	} return true; }
	
	public function MenuForm(Player $sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createSimpleForm(function (Player $sender, ?int $data = null){
			$result = $data;
			if($result === null){
				return;
			}
			switch($result){
				case 0:
				$this->HuongDanForm($sender);
				break;
				case 1:
				$this->TopIslandForm($sender);
				break;
				case 2:
				$this->ThongTinForm($sender);
				break;
			}
		});
		$player = $sender->getName();
		$level = $this->level->get($player);
		$form->setTitle("§l§6Cấp Đảo");
		$form->setContent("§l§3●§e Level của bạn: §a" . $level);
		$form->addButton("§l§3●§a Hứớng Dẫn Level Island §3•");
		$form->addButton("§l§3●§a Top Level Island §3•");
		$form->addButton("§l§3●§a Thông Tin Của Bạn §3•");
		$form->sendToPlayer($sender);
		return $form;
	}
	
	public function HuongDanForm(Player $sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createSimpleForm(function (Player $sender, ?int $data = null){
			$result = $data;
			if($result === null){
				$this->MenuForm($sender);
				return;
			}
			switch($result){
				case 0:
				$this->MenuForm($sender);
				break;
			}
		});
		$msg1 = "§c♦ §eCách sử dụng: Bạn chỉ cần đặt ra nhiều block xuống đất sẽ thu thập được XP sau khi đủ XP bạn đã được lên cấp\n\n";
		$msg2 = "§c♦ §eBấm /levelisland để xem cấp đảo của bạn\n";
		$msg = $msg1 . $msg2;
		$form->setTitle("§l§6Cấp Đảo");
		$form->setContent($msg);
		$form->addButton("§l§3• §aSubmit §3•");
		$form->sendToPlayer($sender);
		return $form;
	}
	
	public function TopIslandForm(Player $sender){
		$levelplot = $this->level->getAll();
		$message = "";
		$message1 = "";
		if(count($levelplot) > 0){
			arsort($levelplot);
			$i = 1;
			foreach($levelplot as $name => $level){
				$message .= "§l§d♦ §aHạng §e".$i."§f: §b".$name." §avới §f".$level." §elevel.\n\n";
				$message1 .= "§l§d♦ §aHạng §e".$i."§f: §b".$name." §avới §f".$level." §elevel.\n\n";
				if($i >= 5){
					break;
				}
				++$i;
			}
		}
		
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createSimpleForm(function (Player $sender, ?int $data = null){
			$result = $data;
			if($result === null){
				$this->MenuForm($sender);
				return;
			}
			switch($result){
				case 0:
				$this->MenuForm($sender);
				break;
			}
		});
		$form->setTitle("§l§6Cấp Đảo");
		$form->setContent("§l§d♦ §aThe Top 5 Island on this Server:\n\n".$message);
		$form->addButton("§l§3• §aSubmit §3•");
		$form->sendToPlayer($sender);
		return $form;
	}
	
	public function ThongTinForm(Player $sender){
		$formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $formapi->createSimpleForm(function (Player $sender, ?int $data = null){
			$result = $data;
			if($result === null){
				$this->MenuForm($sender);
				return;
			}
			switch($result){
				case 0:
				$this->MenuForm($sender);
				break;
			}
		});
		$player = $sender->getName();
		$msg1 = "§c♦ §eNgười chơi§f: " . $player . "\n\n";
		$msg2 = "§c♦ §eLevel Island hiện tại§f: " . $this->level->get($player) . "\n\n";
		$msg3 = "§c♦ §eXP Island Hiện Tại§f: " . $this->exp->get($player) . "/" . $this->nextexp->get($player) . "\n";
		$msg = $msg1 . $msg2 . $msg3; 
		$form->setTitle("§l§6Cấp Đảo");
		$form->setContent($msg);
		$form->addButton("§l§3• §aSubmit §3•");
		$form->sendToPlayer($sender);
		return $form;
	}
	
	public function getLevelIsland($sender){
		if($sender instanceof Player){
			$name = $sender->getName();
			$levelisland = $this->level->get($name);
			return $levelisland;
		}
	}
}