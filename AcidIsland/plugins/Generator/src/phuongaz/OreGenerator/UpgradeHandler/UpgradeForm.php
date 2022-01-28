<?php

namespace phuongaz\OreGenerator\UpgradeHandler;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\nbt\tag\IntTag;
use onebone\economyapi\EconomyAPI;
use onebone\coinapi\CoinAPI;
use jojoe77777\FormAPI\{SimpleForm, CustomForm, ModalForm};
use pocketmine\item\Item;

Class UpgradeForm{

	/** @var Player $player*/
	private $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function init() :void{
		$form = new SimpleForm(function(Player $player, ?int $data){
			if(is_null($data));
			if($data == 0) $this->upgradeForm();
			if($data == 1) $this->buyForm();
		});
		$form->setTitle("§6§lOREGEN");
		$form->addButton("§l§3●§e Nâng Cấp §3●");
		$form->addButton("§l§3●§e Mua OreGen §3●");
		$form->sendToPlayer($this->player);
	}

	public function upgradeForm() :void{
		$inv = $this->player->getInventory();
		$item = $inv->getItemInHand();
		if($item->getId() !== 245){
			$form = new CustomForm(function(Player $player, ?array $data){});
			$form->setTitle("§6§lNâng Cấp OreGen");
			$form->addLabel("§l§cBạn không cầm trên tay Block Tạo Khoảng Sản\n§l§cnên việc nâng cấp đã thất bại !");
			$form->sendToPlayer($this->player);
			return;
		}
		$nbt = $item->getNamedTag();
		if($nbt->hasTag("generator", IntTag::class)){
            $level = $nbt->getInt("generator");
        }else $level = 1;
        $nbt->setInt("generator", $level);
		$next = ($nbt->getInt("generator"))+1;
		$price = (200000 * $next) * $item->getCount();
		$form = new ModalForm(function(Player $player, ?bool $data) use ($inv, $item, $nbt, $next, $price){
			if($data){
				if($nbt->hasTag("generator", IntTag::class)){
					$nbt->setInt("generator", $next);
					if(EconomyAPI::getInstance()->myMoney($player) >= $price){
						if($next == 15){
							$player->sendMessage("§l§3●§e Oregen của bạn đã lên max level.");
							return;
						}
						$item->setNamedTag($nbt);
						$item->setCustomName("§e§l⊹⊱§bOreGen §f(§c".$next."§f)§e⊰⊹");
						$inv->setItemInHand($item);
						EconomyAPI::getInstance()->reduceMoney($player, $price);
						$player->sendMessage("§l§3●§e Đã nâng cấp oregen lên cấp §c ". $next);
						return;
					}
					$player->sendMessage("§l§3●§e Bạn không đủ tiền để nâng cấp.");
				}
			}
		});
		$form->setTitle("§6§lUpgrade Generator");
		$content = "";
		$content .= "§l§3●§e Bạn có muốn nâng cấp lên level : ".$next." ?\n";
		$content .= "§l§3●§e Giá: $price ";
		$form->setContent($content);
		$form->setButton1("§l§3●§e Có §3●");
		$form->setButton2("§l§3●§e Không §3●");
		$form->sendToPlayer($this->player);
	}

	public function buyForm() :void{
		$form = new CustomForm(function(Player $player, ?array $data){
			if(is_null($data)) $this->init();
			if(isset($data[0])){
				$modal = new ModalForm(function(Player $player, ?bool $bool) use ($data){
					if(is_null($bool)) $this->buyForm();
					if($bool){
						if(CoinAPI::getInstance()->myCoin($player) >= $data[0] *20){
							$inv = $player->getInventory();
							$item = Item::get(245, 0, $data[0]);
							$nbt = $item->getNamedTag();
							$nbt->setInt("generator", 1);
							$item->setNamedTag($nbt);
							$item->setCustomName("§l§fMáy Tạo Khoảng Sản §e(§6Level: §c1§e)");
							if($inv->canAddItem($item)){
								$inv->addItem($item);
								CoinAPI::getInstance()->reduceCoin($player, $data[0] *10);
								$player->sendMessage("§6§l【 §fuɴικ §6ғᴀмιʟʏ §6§l】 §aXong!");	
							}else{
								$player->sendMessage("§6§l【 §fuɴικ §6ғᴀмιʟʏ §6§l】 §cTúi đồ đã đầy!");
							}
						}
					}
				});
				$modal->setTitle("§l§eXÁC NHẬN MUA");
				$modal->setContent("§l§f●§c Bạn có muốn mua ". $data[0] . ' oregen với ' .$data[0] *10 .' coin');
				$modal->setButton1("Yes");
				$modal->setButton2("No");
				$modal->sendToPlayer($this->player);
			}
		});
		$form->setTitle("§l§eSHOP OREGEN");
		$form->addSlider("§l§f●§c 20P/1 Oregen", 1, 10);
		$form->sendToPlayer($this->player);
	}
}