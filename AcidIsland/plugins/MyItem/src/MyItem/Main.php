<?php

namespace MyItem;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\command\Command;
use pocketmine\command\Commandsender;

use pocketmine\item\ItemFactory;
use pocketmine\item\Item;

use pocketmine\utils\TextFormat;
use pocketmine\event\player\{PlayerItemConsumeEvent, PlayerItemHeldEvent, PlayerInteractEvent};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use MyItem\customenchants\PiggyCustomEnchantsLoader;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\utils\Config;
use MyItem\utils\Utils;

class main extends PluginBase implements Listener {
	#KHỞI DỘNG
	public static $a;

	const PREFIX = "§6§6[§aMyItem§6]: ";

	public const ITEM_FORMAT = [
		"name" => "",
        "id" => 1,
        "damage" => 0,
        "count" => 1,
        "display_name" => "",
        "lore" => [

        ],
        "enchants" => [

        ],
    ];

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->saveResource("database.yml");
		$this->saveResource("message.yml");
		$this->database = new Config($this->getDataFolder() . "database.yml", Config::YAML);
		$this->message = new Config($this->getDataFolder() . "message.yml");
		$this->saveDefaultConfig();
		$pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents($this, $this);
		$this->getLogger()->info("§l§b=========§aON§b========");
		$this->getLogger()->info("§l§aMyItem "  .$this->getMessage("message.version"));
		$this->getLogger()->info("§eAuthor: §6AnhKhoaaa");
		$this->getLogger()->info("§l§b===================");
		
	#TẮT
	}
		public function onDisable(){
		$this->getLogger()->info("§l§b========§cOFF§b========");
		$this->getLogger()->info("§l§aMyItem ".$this->getMessage("message.version"));
		$this->getLogger()->info("§eAuthor: §6AnhKhoaaa");
		$this->getLogger()->info("§l§b===================");
	}	

	public function onHold(PlayerItemHeldEvent $e){
    	$p = $e->getPlayer();
    	$item = $e->getItem();
    	if($item->getId() >= 1){
    		$p->sendPopup("");
    	}
    }
    
	#COMMAND
	public function onCommand(Commandsender $sender, Command $cmd, string $label, array $args) : bool{
		if($sender instanceof Player){
			$player = $sender->getPlayer();
			$itemname = $player->getInventory()->getItemInHand();
			switch($cmd->getName()){
				case "mi":
				if($player->hasPermission("op.myitem")){	
					if(!isset($args[0])){
						$player->sendMessage(self::PREFIX . $this->getMessage("message.help"));
						$volume = mt_rand();
	        			$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
						return true;
					} else {
						switch(strtolower($args[0])){
							case "reload":
							$this->reload();
							$sender->sendMessage("§a§lReload config successfully!");
							$volume = mt_rand();
			    					$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
							break;
							case "help":
								$player->sendMessage($this->getMessage("message.default-help"));
								$volume = mt_rand();
                				$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_CLICK, (int) $volume);
							break;
							case "info":
								$player->sendMessage("§6-------------");
								$player->sendMessage("§7＞ " . $this->getMessage("message.version"));
								$player->sendMessage("§7＞ §eAuthor: §6AnhKhoaaa");
								$player->sendMessage("");
								$player->sendMessage("§7＞ §ePlugin này ra đời để giúp các ADMIN server có thể tạo ra các trade đồ một cách dễ dàng hơn : D");
								$player->sendMessage("§6-------------");
								$volume = mt_rand();
                				$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_CLICK, (int) $volume);
							break;
							case "create":
								if (!isset($args[1])){
									$player->sendMessage(self::PREFIX . $this->getMessage("create.help"));
									break;
								} else {
									if (!is_numeric($args[1])){
										$player->sendMessage(self::PREFIX . $this->getMessage("create.nonenum"));
									} else {
										$item = Item::get($args[1], 0, 1);
										$player->getInventory()->addItem($item);
										$player->sendMessage(self::PREFIX. $this->getMessage("create.success0") . $args[1] . $this->getMessage("create.success1"));
										$volume = mt_rand();
			    						$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
									}
								}
							break;
							case "name":
							case "setname":
							if (!isset($args[1])){
									$player->sendMessage(self::PREFIX . $this->getMessage("setname.help"));
									$volume = mt_rand();
	        						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
									break;
								} else {
									if($itemname->isNull()){
            							$sender->sendMessage(self::PREFIX . $this->getMessage("message.none-item"));
            							$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
            							break;
        							}
									array_shift($args);
                                    $itemname->setCustomName(str_replace(["&", "{line}"], ["§", "\n"], trim(implode(" ", $args))));
                                    $player->getInventory()->setItemInHand($itemname);
									$player->sendMessage(self::PREFIX . $this->getMessage("setname.success"));
									$volume = mt_rand();
			    					$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
								}
							break;
							case "lore":
							case "setlore":
							if (!isset($args[1])){
									$player->sendMessage(self::PREFIX . $this->getMessage("setlore.help"));
									$volume = mt_rand();
	        						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								} else {
									if($itemname->isNull()){
            							$sender->sendMessage(self::PREFIX . $this->getMessage("message.none-item"));
            							$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
            							break;
        							}
        							array_shift($args);
        							$itemname->setLore([(str_replace(["&", "{line}"], ["§", "\n"], trim(implode(" ", $args))))]);
                                    $player->getInventory()->setItemInHand($itemname);
									$player->sendMessage(self::PREFIX . $this->getMessage("setlore.success"));
									$volume = mt_rand();
			    					$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
								}
							break;
							case "adden":
							case "addenchant":
								if (!isset($args[1])){
									$player->sendMessage(self::PREFIX . $this->getMessage("addenchant.help"));
									$volume = mt_rand();
	        						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);	
									break;
								} else {
									if (!isset($args[2])){
										$args[2] = 1;
									}
									if($itemname->isNull()){
            							$sender->sendMessage(self::PREFIX . $this->getMessage("message.none-item"));
            							$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
            							break;
        							}
        							if (!is_numeric($args[1])){
        								$sender->sendMessage(self::PREFIX . $this->getMessage("addenchant.error-num-en"));
        								$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
        								break;
        							}
        							if (!is_numeric($args[2])){
        								$sender->sendMessage(self::PREFIX . $this->getMessage("addenchant.error-level"));
        								$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
        								break;
        							} else {
        								if ($args[2] > 30000){
        									$sender->sendMessage(self::PREFIX . $this->getMessage("addenchant.error-level-30000"));
        									$volume = mt_rand();
	        								$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
        									break;
        								}
        							}
        							$enchantment = Enchantment::getEnchantment((int)$args[1]);
        							if(!($enchantment instanceof Enchantment)){
            							$sender->sendMessage(self::PREFIX . $this->getMessage("addenchant.error-num-en"));
            							break;
        							}
		                			$enchInstance = new EnchantmentInstance($enchantment, (int)$args[2]);
									$itemname->addEnchantment($enchInstance);
									$player->getInventory()->setItemInHand($itemname);
									$player->sendMessage(self::PREFIX . $this->getMessage("addenchant.success"));
									$volume = mt_rand();
			    					$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
								}
							break;
							case "id":
							case "idenchant":
								$player->sendMessage($this->getMessage("idenchant.id"));
								$volume = mt_rand();
                				$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_CLICK, (int) $volume);
							break;
							case "itemsave":
							case "save":
							if (!isset($args[1])){
									$player->sendMessage(self::PREFIX . $this->getMessage("save.help"));
									$volume = mt_rand();
	        						$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
									break;
								} else {
									if($itemname->isNull()){
            							$sender->sendMessage(self::PREFIX . $this->getMessage("message.none-item"));
            							$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
            							break;
        							}
        							array_shift($args);
        							self::$a = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
        							if($this->database->exists(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))))){       								
        								$sender->sendMessage(self::PREFIX . $this->getMessage("save.already-name-save"));
        								$volume = mt_rand();
	        							$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
        								break;	
        							}
        							$this->database->set(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))), $this->itemToData($itemname));
        							$this->database->save();
        							$sender->sendMessage(self::PREFIX . $this->getMessage("save.success"));
        							$volume = mt_rand();
			    					$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
        						}
							break;
							case "itemtake":
							case "take":
							if (!isset($args[1])){
								$player->sendMessage(self::PREFIX . $this->getMessage("take.help1")."\n".self::PREFIX . $this->getMessage("take.help2"));
								$volume = mt_rand();
	        					$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								break;
							}
							array_shift($args);
							if (!$this->database->exists(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))))){
								$player->sendMessage(self::PREFIX . $this->getMessage("message.not-found-item"));
								$volume = mt_rand();
	        					$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								break;
							}
							$item = $this->dataToItem($this->database->get(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)))));
							$player->getInventory()->addItem($item);
							$player->sendMessage(self::PREFIX . $this->getMessage("take.success"));
							$volume = mt_rand();
			    			$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
							$this->database->save();
							break;
							case "itemdelete":
							case "delete":
							if (!isset($args[1])){
								$player->sendMessage("§6[§aMyItem§6]: §7Hãy nhập: §6/mi delete §7[TÊN CẦN XÓA].");
								$volume = mt_rand();
	        					$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								break;
							}
							array_shift($args);
							if (!$this->database->exists(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))))){
								$player->sendMessage(self::PREFIX . $this->getMessage("message.not-found-item"));
								$volume = mt_rand();
	        					$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								break;
							} else {
								$this->database->remove(str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args))), [$this->itemToData($itemname)]);
								$player->sendMessage(self::PREFIX . $this->getMessage("delete.success"));
								$volume = mt_rand();
			    				$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
								$this->database->save();
								break;
							}						
							break;
							case "itemlist":
							case "list":
							$i = 1;
							if ($this->database->getAll() == null){
								$player->sendMessage(self::PREFIX . $this->getMessage("list.none-item"));
								$volume = mt_rand();
	        					$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								break;
							}
							$player->sendMessage(self::PREFIX . $this->getMessage("list.save"));
							foreach($this->database->getAll() as $names){
								foreach($names as $name=>$val){
									if ($name != "name"){
										continue;
									}
									$player->sendMessage(" §7". $i .">§d ". $val. "§7.");
									$i++;
								}
							}
							$volume = mt_rand();
                			$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_CLICK, (int) $volume);
							$this->database->save();
							break;
							case "takeall":
							if ($this->database->getAll() == null){
								$player->sendMessage(self::PREFIX . $this->getMessage("list.none-item"));
								$volume = mt_rand();
	        					$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
								break;
							}
							foreach($this->database->getAll() as $names){
								$item = $this->dataToItem($names);
								$player->getInventory()->addItem($item);
							}
							break;
						}
					}
				} else {
					$player->sendMessage(self::PREFIX . $this->getMessage("message.no-permission"));
					$volume = mt_rand();
	        		$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
				}
			}
		} else {
			$sender->sendMessage(self::PREFIX . $this->getMessage("use-in-game"));
		}
		return true;
	}



    /**
     * @param array $itemData
     * @return Item
     */
    public static function dataToItem(array $itemData) : Item {
        $item = ItemFactory::get($itemData["id"], $itemData["damage"] ?? 0, $itemData["count"] ?? 1);
                if(isset($itemData["enchants"])) {
                    foreach($itemData["enchants"] as $ename => $level) {
                        $ench = Enchantment::getEnchantment((int)$ename);
                        if(PiggyCustomEnchantsLoader::isPluginLoaded() && $ench === null) {

                            if(!PiggyCustomEnchantsLoader::isNewVersion()) $ench = CustomEnchants::getEnchantment((int)$ename);
                            else $ench = CustomEnchantManager::getEnchantment((int)$ename);

                        }
                        if($ench === null) continue;
                        if(!PiggyCustomEnchantsLoader::isNewVersion() && $ench instanceof CustomEnchants) {
                            PiggyCustomEnchantsLoader::getPlugin()->addEnchantment($item, $ench->getName(), $level);
                        } else {
                            $item->addEnchantment(new EnchantmentInstance($ench, $level));
                        }
                    }
                }
                if(isset($itemData["display_name"])) $item->setCustomName(TextFormat::colorize($itemData["display_name"]));
                if(isset($itemData["lore"])) {
                    $lore = [];
                    foreach($itemData["lore"] as $key => $ilore) {
                        $lore[$key] = TextFormat::colorize($ilore);
                    }
                    $item->setLore($lore);
                }
                return $item;

    }
    public function reload()
    {
        $this->database->reload();
        $this->saveDefaultConfig();
        $this->database->save();
    }

    /**
     * @param Item $item
     * @return array
     */
    public static function itemToData(Item $item) : array {
        $itemData = self::ITEM_FORMAT;
        $itemData["name"] = self::$a;
        self::$a = "";
        $itemData["id"] = $item->getId();
        $itemData["damage"] = $item->getDamage();
        $itemData["count"] = $item->getCount();
            if($item->hasCustomName()) {
                $itemData["display_name"] = $item->getCustomName();
            } else {
                unset($itemData["display_name"]);
            }
            if($item->getLore() !== []) {
                $itemData["lore"] = $item->getLore();
            } else {
                unset($itemData["lore"]);
            }
            if($item->hasEnchantments()) {
                foreach($item->getEnchantments() as $enchantment) {
                    $itemData["enchants"][(string)$enchantment->getId()] = $enchantment->getLevel();
                }
            } else {
                unset($itemData["enchants"]);
            }
        return $itemData;
    }

    public function getMessage(string $key, array $tags = []): string
    {
        return Utils::translateColorTags(str_replace(array_keys($tags), $tags, $this->message->getNested($key, $key)));
    }
}

