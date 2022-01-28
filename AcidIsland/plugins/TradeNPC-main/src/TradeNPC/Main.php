<?php
declare(strict_types=1);

namespace TradeNPC;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\item\{Item, ItemFactory};
use pocketmine\nbt\LittleEndianNBTStream;
use muqsit\invmenu\{InvMenu, InvMenuHandler};
use pocketmine\command\{CommandSender, Command};
use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\nbt\tag\{ByteArrayTag, CompoundTag, ListTag, StringTag};
use pocketmine\network\mcpe\protocol\{LevelEventPacket, LevelSoundEventPacket};
use pocketmine\event\player\{PlayerMoveEvent, PlayerQuitEvent, PlayerChatEvent};
use muqsit\invmenu\transaction\{InvMenuTransaction as Transaction, InvMenuTransactionResult as TransactionResult};
use pocketmine\network\mcpe\protocol\{ActorEventPacket, ContainerClosePacket, InventoryTransactionPacket, LoginPacket};
use pocketmine\network\mcpe\protocol\types\inventory\{UseItemOnEntityTransactionData, NormalTransactionData, TransactionData};


class Main extends PluginBase implements Listener
{

	public const CHEST = [
		0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26
	];

	public const ITEM_FORMAT = [
        "id" => 1,
        "damage" => 0,
        "count" => 1,
        "display_name" => "",
        "lore" => [

        ],
        "enchants" => [

        ],
    ];

	protected $deviceOSData = [];

	public $fullItem = [];
	public $name = null;
	public $start = false;

	public $turn = false;

	public $enti = null;

	private static $instance = null;

	public $itemList = [];

	public function onLoad()
	{
		self::$instance = $this;
	}

	public static function getInstance(): Main
	{
		return self::$instance;
	}

	public function onEnable()
	{
		$this->saveResource("config.yml");
		Entity::registerEntity(TradeNPC::class, true, ["tradenpc"]);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
		$this->menu = InvMenu::create(InvMenu::TYPE_CHEST);
	}

	public function onChat(PlayerChatEvent $ev){
		$p = $ev->getPlayer();
		$chat = $ev->getMessage();
		if($this->turn and $this->name == $p->getName()){
        	$entity = $this->getEntityName($chat);
        	for($i=0;$i <= $this->fullItem;$i++){
        		$item1 = $this->fullItem[$i];
        		$item2 = $this->fullItem[$i+9];
        		$item3 = $this->fullItem[$i+18];
        		if($item1->isNull() or $item2->isNull() or $item3->isNull()){
        			unset(TradeDataPool::$editNPCData[$p->getName()]);
        			break;
        		}
        		TradeDataPool::$editNPCData[$p->getName()] ["buyA"] = $item1;
				TradeDataPool::$editNPCData[$p->getName()] ["buyB"] = $item2;
				TradeDataPool::$editNPCData[$p->getName()] ["sell"] = $item3;
				$buya = TradeDataPool::$editNPCData[$p->getName()] ["buyA"];
				$buyb = TradeDataPool::$editNPCData[$p->getName()] ["buyB"];
				$sell = TradeDataPool::$editNPCData[$p->getName()] ["sell"];
				$entity->addTradeItem($buya, $buyb, $sell);
				unset(TradeDataPool::$editNPCData[$p->getName()]);
				$p->sendMessage("Đã thêm item vào trade!");
        	}
        	$this->fullItem = [];
        	$this->turn = false;
        	$this->name = null;
        	unset(TradeDataPool::$editNPCData[$p->getName()]);
			$ev->setCancelled();
        }
	}

	public function getEntityName(string $chat){
		foreach ($this->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $entity) {
				if ($entity instanceof TradeNPC) {
					if ($entity->getNameTag() === $chat) {
						$this->turn = false;
						return $entity;
						break;
					}
				}
			}
		}
	}

	public function setItemss($p){
		$this->menu->setName("§eTradeNPC");
		$this->menu->setListener(function(Transaction $transaction) : TransactionResult{
			$inv = $this->menu->getInventory();
            $player = $transaction->getPlayer();
            if($transaction->getItemClicked()->getId() == 160){
                foreach(self::CHEST as $slot){
                	$item = $this->menu->getInventory()->getItem($slot);
                	if($item->getId() == 160){
                		continue;
                	}
                	$this->fullItem[] = $item;
                }
                $this->turn = true;
                $this->name = $player->getName();
                $player->sendMessage("Nhập Tên NPC");
                $player->removeWindow($inv);
                return $transaction->discard();
            }
            return $transaction->continue();
		});
		$xacnhan = Item::get(160,7,1);
		$xacnhan->setCustomName("Xác nhận\nKhi bấm rồi nhập tên NPC");
		$thoat = Item::get(331,0,1);
		$thoat->setCustomName("Thoát");
		$inv = $this->menu->getInventory();
		$inv->setItem(26, $xacnhan);
		$this->menu->send($p);
	}

	public function loadData(TradeNPC $npc)
	{
		if (file_exists($this->getDataFolder() . $npc->getNameTag() . ".dat")) {
			$nbt = (new LittleEndianNBTStream())->read(file_get_contents($this->getDataFolder() . $npc->getNameTag() . ".dat"));
		} else {
			$nbt = new CompoundTag("Offers", [
				new ListTag("Recipes", [])
			]);
		}
		$npc->loadData($nbt);
	}

	public function onMove(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		if ($this->getConfig()->getNested("enable-see-player", false)){
			foreach ($player->getLevel()->getEntities() as $entity) {
				if ($entity instanceof TradeNPC) {
					if ($player->distance($entity) <= 5) {
						$entity->lookAt($player);
					}
				}
			}
		}
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		if (!$sender instanceof Player) {
			return true;
		}
		if (!isset($args[0])) {
			$args[0] = "x";
		}
		if($sender->getName() == "S2TwKen" or $sender->getName() == "STGhast"){
		switch ($args[0]) {
			case "create":
				array_shift($args);
				$name = array_shift($args);
				if (!isset($name)) {
					$sender->sendMessage("Hãy sử dụng: /npc create (tên)");
					break;
				}
				$nbt = Entity::createBaseNBT($sender->asVector3());
				$nbt->setTag(new CompoundTag("Skin", [
					new StringTag("Name", $sender->getSkin()->getSkinId()),
					new ByteArrayTag("Data", $sender->getSkin()->getSkinData()),
					new ByteArrayTag("CapeData", $sender->getSkin()->getCapeData()),
					new StringTag("GeometryName", $sender->getSkin()->getGeometryName()),
					new ByteArrayTag("GeometryData", $sender->getSkin()->getGeometryData())
				]));
				/** @var TradeNPC $entity */
				$entity = Entity::createEntity("tradenpc", $sender->getLevel(), $nbt);
				$entity->setNameTag($name);
				$entity->spawnToAll();
				break;
			case "setitem":
				$this->setItemss($sender);
				break;
			case "remove":
				array_shift($args);
				$name = array_shift($args);
				if (!isset($name)) {
					$sender->sendMessage("Hãy sử dụng: /npc remove (tên)");
					break;
				}
				if (!file_exists($this->getDataFolder() . $name . ".dat")) {
					$sender->sendMessage("Tên NPC này hiện không tồn tại!");
					break;
				}
				unlink($this->getDataFolder() . $name . ".dat");
				$sender->sendMessage("Đã xóa NPC!");
				foreach ($this->getServer()->getLevels() as $level) {
					foreach ($level->getEntities() as $entity) {
						if ($entity instanceof TradeNPC) {
							if ($entity->getNameTag() === $name) {
								$entity->close();
								break;
							}
						}
					}
				}
				break;
			default:
				foreach ([
							 ["/npc create (tên)", "Tạo ra npc trade"],
							 ["/npc setitem", "Add item vào trade"],
							 ["/npc remove", "Xóa npc"]
						 ] as $usage) {
					$sender->sendMessage($usage[0] . " - " . $usage[1]);
				}
		}
	}
		return true;
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 *
	 * @author
	 */
	public function handleDataPacket(DataPacketReceiveEvent $event)
	{
		$player = $event->getPlayer();
		$packet = $event->getPacket();
		if ($packet instanceof ActorEventPacket) {
			if ($packet->event === ActorEventPacket::COMPLETE_TRADE) {
				if (!isset(TradeDataPool::$interactNPCData[$player->getName()])) {
					return;
				}
				$data = TradeDataPool::$interactNPCData[$player->getName()]->getShopCompoundTag()->getListTag("Recipes")->get($packet->data);
				if ($data instanceof CompoundTag) {
					$buya = Item::nbtDeserialize($data->getCompoundTag("buyA"));
					$buyb = Item::nbtDeserialize($data->getCompoundTag("buyB"));
					$sell = Item::nbtDeserialize($data->getCompoundTag("sell"));
					if ($player->getInventory()->contains($buya) and $player->getInventory()->contains($buyb)) {// Prevents https://github.com/alvin0319/TradeNPC/issues/3
						$player->getInventory()->removeItem($buya);
						$player->getInventory()->removeItem($buyb);
						$player->getInventory()->addItem($sell);
						$volume = mt_rand();
						$player->getlevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
					} else {
						$volume = mt_rand();
	        			$player->getLevel()->broadcastLevelEvent($player, LevelEventPacket::EVENT_SOUND_ANVIL_FALL, (int) $volume);
					}
				}
				// unset(TradeDataPool::$interactNPCData[$player->getName()]);
			}
		}
		if ($packet instanceof InventoryTransactionPacket) {
			//7: PC
			if($packet->trData instanceof NormalTransactionData){
				foreach ($packet->trData->getActions() as $action) {
					if ($action instanceof NetworkInventoryAction) {
						if (isset(TradeDataPool::$windowIdData[$player->getName()]) and $action->windowId === TradeDataPool::$windowIdData[$player->getName()]) {
							$player->getInventory()->addItem($action->oldItem);
							$player->getInventory()->removeItem($action->newItem);
						}
					}
				}
			} elseif($packet->trData instanceof UseItemOnEntityTransactionData) {
				$entity = $player->getLevel()->getEntity($packet->trData->getEntityRuntimeId());
				if ($entity instanceof TradeNPC) {
					$player->addWindow($entity->getTradeInventory());
				}
			}
		}
		if ($packet instanceof LoginPacket) {
			$device = (int)$packet->clientData["DeviceOS"];
			$this->deviceOSData[strtolower($packet->username)] = $device;
		}
		if ($packet instanceof ContainerClosePacket) {
			if (isset(TradeDataPool::$windowIdData[$player->getName()])) {
				$pk = new ContainerClosePacket();
				$pk->windowId = 255; // ??
				$player->sendDataPacket($pk);
			}
		}
	}

	public function onQuit(PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		if (isset($this->deviceOSData[strtolower($player->getName())])) unset($this->deviceOSData[strtolower($player->getName())]);
	}

	public function saveData(TradeNPC $npc)
	{
		file_put_contents($this->getDataFolder() . $npc->getNameTag() . ".dat", $npc->getSaveNBT());
	}

	public function onDisable()
	{
		foreach ($this->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $entity) {
				if ($entity instanceof TradeNPC) {
					file_put_contents($this->getDataFolder() . $entity->getNameTag() . ".dat", $entity->getSaveNBT());
				}
			}
		}
	}
}