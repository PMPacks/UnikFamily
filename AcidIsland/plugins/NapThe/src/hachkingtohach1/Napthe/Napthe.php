<?php

namespace hachkingtohach1\Napthe;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\event\player\PLayerJoinEvent;
use pocketmine\event\Listener;
use jojoe77777\FormAPI\{CustomForm, ModalForm, SimpleForm};
Class Napthe extends PluginBase implements Listener{

	/** @var self $instance */
	public static $instance;
	/**
	THESIEURE:
		ID: 4673357261
		KEY: df9751c4eff4070e9695b4d15bba7150
	TRUMTHE:
		ID: 2326357261
		KEY: 75682e5c7932acb0827cd6de91317822
	NHO DOI URL 2 CAI TASK
	2 web trên có cùng api nạp thẻ nên chỉ cần đổi id,key là đc
	*/
	public $partnerId = "2326357261";
	public $partnerKey = "75682e5c7932acb0827cd6de91317822";
	public $formapi;
	public function onEnable(){

		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->gem = $this->getServer()->getPluginManager()->getPlugin("GemUI");
		@mkdir($this->getDataFolder());
		$this->tl = new Config($this->getDataFolder()."tichluy.yml", Config::YAML);
		$this->saveDefaultConfig();
		$this->getResource("config.yml");
	}

	public function onPlayerJoin(PLayerJoinEvent $e){
		$user = $e->getPlayer()->getName();
		if(!$this->tl->exists($user)){
			$this->tl->set($user, 0);
			$this->tl->save();
		}
	}

	public function onCommand(CommandSender $sender, Command $cmd,string $label, array $args) :bool{
		if($cmd->getName() == "napthe"){
			$this->formapi = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			if($this->formapi == null){ 
				$sender->sendMessage("Thiếu thư viện, hãy báo lỗi admin");
				return true;
			}
			if(!$sender instanceof Player) return true;
			$this->mainform($sender);			
			return true;
		}
		if($cmd->getName() == "topnapthe"){
			$levelplot = $this->tl->getAll();
			$max = 0;
			$max = count($levelplot);
			$max = ceil(($max / 5));
			
			$message = "";
			$message1 = "";
			
			$page = array_shift($args);
			$page = max(1, $page);
			$page = min($max, $page);
			$page = (int)$page;
			
			$aa = $this->tl->getAll();
			arsort($aa);
			$i = 0;
			
			foreach ($aa as $b=>$a) {
				if (($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4) {
					$i1 = $i + 1;
					$trang = "§l§c•§e Trang:§a ".$page."§f/§c".$max."\n";
					$message .= "§l§bHạng§e ".$i1." §bthuộc về§c ".$b." §fvới§e ".$a."§aVNĐ\n";
					$message1 .= "§l§bHạng§e ".$i1." §bthuộc về§c ".$b." §fvới§e ".$a."§aVNĐ\n";
				} $i++;
			}
			$form = new CustomForm(function (Player $sender, $data) use ($trang, $message) {
				if ($data === null) { return false; }
				$this->getServer()->dispatchCommand($sender, "topnapthe " . $data[1]);
			});
			$form->setTitle("§l§e•§6 TOP NẠP THẺ §e•");
			$form->addLabel($trang. $message);
			$form->addInput("§l§3•§a Qua Trang:");
			$form->sendToPlayer($sender);
		}
		return false;
	}
	public function mainform(Player $player){
			$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null) {
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){			
					case "0";	
                     $this->cardinfoForm($player);						
					break;				
                    default:
                    break;					
			}
			});
			$event = $this->getConfig()->get("event");
			$form->setTitle("§l§6Nạp thẻ");
			$txt = 
			"§l§3●§e Chào các member!, hiện tại máy chủ đang có event x{$event}.\n\n".
			"§l§3●§e Máy chủ khuyến khích các bạn nạp thẻ viettel hoặc momo (nếu có).\n\n".
			"§l§3●§e Khi nạp thẻ thành công mà không nhận được Gem vui lòng liên hệ admin của server để giải quyết.\n\n".
			"§l§3●§e Máy Chủ Unik Family.\n"
			;
			$form->setContent($txt);
			$form->addButton("§3§l●§e Thẻ cào §3●§e",0,"textures/ui/mining_fatigue_effect");
			$form->sendToPlayer($player);
			return $form;
	}
	public function momoform(Player $player){
			$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null) {
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					case "0";	                  
                     $this->mainform($player);					 
					break;
					case "1";	
                     						
					break;				
                    default:
                    break;					
			}
			});
			$form->setTitle("§dMoMo§f/§aAtm");
			$txt = 
			"§l§f•Bạn hãy liên hệ §bfacebook§f admin để nhắn tin và chuyển khoản, nếu ko ngại bạn có thể trực tiếp chuyển vào tài khoản bên dưới và chờ phản hồi\n\n".
			"§l§f•§bFacebook: facebook.com/tungstenvn\n".
			"§l§f   §bTên: Tùng Nguyễn\n\n".
			"§l§f•§6Đối với §dMomo §6bạn không cần thẻ §aATM§6, bạn chỉ cần cầm tiền mặt ra §l§bF§6P§aT§6SHOP§r§6§l gần nhất, tới bảo họ §achuyển tiền§6 vô momo, thì chỉ cần đọc §aSĐT§6 bên dưới,rồi đưa §atiền mặt§6 cho thu ngân là xong\n\n".
			"§l§f•§dMomo:\n".
			"§l§f   SDT: xxx\n".
			"§l§f   Tên: xxxx\n\n".
			"§l§f•§aAtm:\n".
			"§l§f   Ngân hàng: Vietcombank\n".
			"§l§f   STK: xxx\n".
			"§l§f   Tên: xxx\n\n".
			"§l§f•§6Tin nhắn: \n   §fHãy gửi kèm tin nhắn §aghi tên nhân vật§f trong server của bạn\n\n";
			$form->setContent($txt);
			$form->addButton("§l§3●§e Quay lại §l§3●",0,"textures/ui/ps4_dpad_right");
			$form->addButton("§l§3●§e Thoát §l§3●",0,"textures/ui/Ping_Offline_Red_Dark");
			$form->sendToPlayer($player);
			return $form;
	}
	public $chuyendoi =
	[
		"10000" => 10,
		"20000" => 25,
		"50000" => 100,
		"100000" => 240,
		"200000" => 480,
		"500000" => 1000
	];
	public function cardinfoForm(Player $player){
		$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					case "0";	                  
						$this->thecaoform($player);
					break;
					case "1";						
					break;				
                    default:
                    break;					
			}
			});
		$d1 = 10*$this->getConfig()->get("event");
		$d2 = 25*$this->getConfig()->get("event");
		$d3 = 100*$this->getConfig()->get("event");
		$d4 = 240*$this->getConfig()->get("event");
		$d5 = 480*$this->getConfig()->get("event");
		$d6 = 1000*$this->getConfig()->get("event");
			$form->setTitle("§l§6Nạp Thẻ");
			$txt = 
			"§e§l●§c Vui lòng chọn đúng mệnh giá khi chọn sai admin không chịu trách nhiệm!\n\n".
			"§e§l●§e Bảng giá\n\n".
			"§l§f   ➻ §a10.000đ §f= §e{$d1} §cGem\n\n".
			"§l§f   ➻ §a20.000đ §f= §e{$d2} §cGem\n\n".
			"§l§f   ➻ §a50.000đ §f= §e{$d3} §cGem\n\n".
			"§l§f   ➻ §a100.000đ §f= §e{$d4} §cGem\n\n".
			"§l§f   ➻ §a200.000đ §f= §e{$d5} §cGem\n\n".
			"§l§f   ➻ §a500.000đ §f= §e{$d6} §cGem\n\n";
			$form->setContent($txt);
			$form->addButton("§b§l●§c Tiếp tục §b●§e",0);
			$form->addButton("§b§l●§c Thoát §b●§e",0);
			$form->sendToPlayer($player);
			return $form;
	}
	public function thecaoform(Player $player,string $loaithe = null,string $menhgia = null,string $seri = null,string $pin = null){
		$loaithe_arr = ["Viettel", "Vnmobi","Zing"];
		$menhgia_arr = ["10000","20000","50000","100000", "200000", "500000"];
		$form = $this->formapi->createCustomForm(function (Player $player, $data) use ($loaithe_arr,$menhgia_arr){
			$result = $data;
			if ($result === null) {
				return true;
			}
			$telco = $loaithe_arr[$result[1]];
			$menhgia = $menhgia_arr[$result[2]];
			$seri = $result[3];
			$pin = $result[4];
			$thongtin = [$telco,$menhgia,$seri,$pin];
			$this->xacnhanform($player,$thongtin);
		});
		
		$form->setTitle("§l§6Nạp thẻ");
		$form->addLabel(
			"§e§l●§c Điền các thông tin sau lưu ý: hãy chọn đúng mệnh giá khi sai admin không chịu trách nhiệm", 
		);
		$form->addDropdown("§e§l●§6 Loại thẻ: ",$loaithe_arr,(int) array_search($loaithe, $loaithe_arr));
		$form->addDropdown("§e§l●§6 Mệnh giá: ",$menhgia_arr,(int) array_search($menhgia, $menhgia_arr));
		$form->addInput("§e§l●§6 Seri: ","",$seri);
		$form->addInput("§e§l●§6 Mã thẻ: ","",$pin);		
		$form->sendToPlayer($player);
		return $form;
	}
	public function xacnhanform(Player $player, array $thongtin){
			$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null) use($thongtin) {
			$result = $data;
			if ($result === null) {
				return true;
			}
			switch($result){				
					case "0";
						$player->sendTip("§e§l●§c Đang kiểm tra thẻ...");					
						$this->getServer()->getAsyncPool()->submitTask(new NaptheTask([$this->partnerId,$this->partnerKey],strtoupper($thongtin[0]),(string) $thongtin[3],(string) $thongtin[2],(int)$thongtin[1],$player->getName()));			 
					break;
					case "1";
						$this->thecaoform($player,$thongtin[0],$thongtin[1],$thongtin[2],$thongtin[3]);
					break;
					case "2";                  						
					break;					
                    default:
                    break;					
			}
			});
			$form->setTitle("§6§lThông tin thẻ");
			$txt = 
			"§e§l●§6 Loại thẻ: ".$thongtin[0]."\n\n".
			"§e§l●§6 Mệnh giá: ".$thongtin[1]."\n\n".
			"§e§l●§6 Seri: ".$thongtin[2]."\n\n".
			"§e§l●§c Mã thẻ: ".$thongtin[3]."\n\n";
			$form->setContent($txt);
			$form->addButton("§b§l●§c Nạp §b●§e",0);
			$form->addButton("§b§l●§c Quay lại §b●§e",0);
			$form->addButton("§b§l●§c Thoát §b●§e",0);
			$form->sendToPlayer($player);
			return $form;
	}
	public function onSuccess(Player $player,string $txt){
		$form = $this->formapi->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if ($result === null) {
				return true;
			}
			});
			$form->setTitle("§l§6Thông tin");
			$form->setContent($txt);
			$form->addButton("§b§l●§c Thoát §b●§e",-1);
			$form->sendToPlayer($player);
			return $form;
		
	}
	
	public function napThanhCong(string $name,int $giatri, int $Gem){	
		$player = $this->getServer()->getPlayerExact($name);
		
		$this->getServer()->broadcastMessage("§6§l【 §fuɴικ §6ғᴀмιʟʏ §6§l】 §6Hôm nay §cĐại gia §f{$name}§6 đã nạp §a{$giatri}§6 để ủng hộ server!");				
		
		//event x2:
		$Gem1 = $Gem*$this->getConfig()->get("event");
		$this->gem->addGem($player, $Gem1);
		
		$totalmoney = $this->getConfig()->getNested("database.$name.totalmoney");
        if(!isset($totalmoney)){
            $this->getConfig()->setNested("database.$name.totalmoney",$giatri);
			$this->getConfig()->setNested("database.$name.Gem",$Gem);
        }else{
			$coin = $this->getConfig()->getNested("database.$name.Gem");
			$this->getConfig()->setNested("database.$name.totalmoney",$totalmoney + $giatri);
			$this->getConfig()->setNested("database.$name.Gem",$Gem + $coin);
		}
		$this->tl->set($name, $this->tl->get($name) + $giatri);
		$this->tl->save();
        $this->getConfig()->save();
		if($player == null){
			return;
		}
		$txt = 
		"§e§l●§c Nạp thẻ thành công\n\n".
		"§e§l●§c Mệnh giá: ".$giatri." đồng\n\n".
		"§e§l●§c Nhận được: ".((int) $Gem)." Gem\n\n".
		"§e§l●§6 Cảm ơn bạn đã donate tại unik family\n\n";
		$this->onSuccess($player,$txt);
	}
}