<?php

declare(strict_types=1);

namespace CLADevs\Minion\minion;

use CLADevs\Minion\Main;
use pocketmine\block\Block;
use pocketmine\block\Chest;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{LevelEventPacket,AnimatePacket};
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;
use onebone\economyapi\EconomyAPI;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\item\Tool;

use pocketmine\item\Pickaxe;
use pocketmine\item\Axe;
use pocketmine\item\TieredTool;

use pocketmine\inventory\Inventory;
use CLADevs\Minion\database\Vault;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};

use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\block\Liquid;
class Minion extends Human{

    protected $minionname = "";
    protected $breaktime = 0;
    protected $inv;

    private $player;
    private $time = 0;
    private $owner;
    private $delay = null;
    private $ore = [14, 15, 16, 73, 56, 129];
    private $ores = array(
     14 => 266,
     15 => 265,
     16 => 263,
     73 => 331,
     56 => 264,
     129 => 388
    );

    private $is_ore = false, $is_chest = false, $is_sell = false;

    private $check = null;
    
    public function initEntity(): void{
        parent::initEntity();
        $this->player = $this->namedtag->getString("player");
        $this->minionname = "§l§eMINION\n" ."§f(§a" .$this->player."§f)";
        $this->setHealth(1);
        $this->setMaxHealth(1);
        $this->setNameTagAlwaysVisible();
        $this->setNameTag($this->minionname);
        $this->setScale((float)0.7);
        $skin = Main::get()->getSkin();
        $this->setSkin($skin);
        if(($sell = $this->namedtag->getString("SELL")) == "yes"){
            $this->is_sell = true;
        }
        if(($ore = $this->namedtag->getString("ORE")) == "yes"){
            $this->is_ore = true;
        }    
    }

    public function setDefaultPos(Vector3 $vec) :void{
        $this->savePos = $vec;
    }

    public function getDefaultPos() :?Vector3{
        return $this->savePos;
    }

    public function attack(EntityDamageEvent $source): void{
        $source->setCancelled();
        if($source instanceof EntityDamageByEntityEvent){
            $damager = $source->getDamager();
            if($damager instanceof Player){
                $name = Main::get()->checkName($damager);
                if($name !== $this->player){
                    if(!$damager->hasPermission("minion.open.others")){
                        $damager->sendMessage(C::RED . "Đây không phải Minion của bạn.");
                        return;
                    }
                    Main::get()->sendStaffForm($damager, $this->player, $this);
                    return;
                }
                Main::get()->sendForm($damager, $this);
            }
        }
    }

    public function setOwner(Player $player) :void{
        $this->owner = $player;
    }

    public function setDelay(int $sc){
        $this->delay = $sc;
    }

    public function getMineInv() : ?Inventory{
        Main::get()->getDatabase()->loadVault($this->player, 1, function(Vault $vault){
            $this->inv = $vault->getInventory();
        });
        return $this->inv;
    }

    public function onBreakBlock() :void{
        if ($this->getLookingBlock()->getId() !== Block::AIR and $this->getLookingBlock()->isSolid()){
            $block = $this->getLookingBlock();
            $breaktime = $block->getBreakTime($this->getInventory()->getItemInHand()) * 20;
            if(ceil($breaktime) > 0){
                $pk = new AnimatePacket();
                $pk->entityRuntimeId = $this->id;
                $pk->action = AnimatePacket::ACTION_SWING_ARM;
                foreach (Server::getInstance()->getOnlinePlayers() as $p) $p->dataPacket($pk);
                $pk = new LevelEventPacket();
                $pk->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
                $pk->position = $block->asVector3();
                $breakTime = ceil($breaktime);
                $pk->data = (int)round(65535 / $breakTime);
                foreach (Server::getInstance()->getOnlinePlayers() as $p) $p->dataPacket($pk);
                if($this->breaktime == 0){
                    $this->breaktime = ceil($breaktime);
                    $pk = new LevelEventPacket();
                    $pk->evid = LevelEventPacket::EVENT_BLOCK_START_BREAK;
                    $pk->position = $block->asVector3();
                    $breakTime = ceil($block->getBreakTime($this->getInventory()->getItemInHand()) * 20);
                    $pk->data = (int)round(65535 / $breakTime);
                    foreach (Server::getInstance()->getOnlinePlayers() as $p) $p->dataPacket($pk);
                }
                if(--$this->breaktime <= 0){
                    $this->breakBlock($block);
                    $this->getLevel()->addParticle(new DestroyBlockParticle($block->asVector3()->add(0.5, 0.5, 0.5), $block));
                    $this->breaktime = 0;
                    $hand = $this->getInventory()->getItemInHand();
                    if($hand->hasEnchantment(15)){
                        if($hand->getEnchantment(15)->getLevel() > 5){
                            $this->setDelay(5);
                        }
                    }
                    $this->setDelay(3);
                }
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool{
        $update = parent::entityBaseTick($tickDiff);
        if($this->delay !== null){
            if(--$this->delay <= 0){
                $this->delay = null;
            }
        }else {
            if($this->getOwner() !== null){
                $this->onBreakBlock();
            }
        }
        if(floor($this->y) < 0){
            if(!is_null($owner = $this->getOwner())){
                if(--$this->time <= 0){
                    $spawn = Main::get()->getItem($owner);
                    if($this->check == null){
                        $owner->sendMessage("§l§e•> §6Hình như Minion của bạn vừa rớt xuống vực");
                        $this->teleport($owner);
                        if($owner->getInventory()->canAddItem($spawn)){
                           Main::get()->flagEntity($owner, $this);  
                        }else{
                            $owner->sendMessage("§l§e•>§6 Túi của bạn đã đầy, Minion không thể quay trở lại vào túi");
                            $owner->sendMessage("§l§e•>§6 Hãy cất bớt vật phẩm"); 
                            $this->check = true;
                        }                    
                    }else{
                        if($owner->getInventory()->canAddItem($spawn)){
                           Main::get()->flagEntity($owner, $this); 
                           $this->check = null;
                        }else{
                            $owner->sendMessage("§l§e•>§6 Túi của bạn đã đầy, Minion không thể quay trở lại vào túi");
                            $owner->sendMessage("§l§e•>§6 Hãy cất bớt vật phẩm");                            
                            $this->check = true;
                        } 
                    }
                    $this->time = 360;                       
                } 
            }
        }
        return $update;
    }

    public function getLookingBlock(): Block{
        $block = Block::get(Block::AIR);
        switch($this->getDirection()){
            case 0:
                $block = $this->getLevel()->getBlock($this->add(1, 0, 0));
                break;
            case 1:
                $block = $this->getLevel()->getBlock($this->add(0, 0, 1));
                break;
            case 2:
                $block = $this->getLevel()->getBlock($this->add(-1, 0, 0));
                break;
            case 3:
                $block = $this->getLevel()->getBlock($this->add(0, 0, -1));
                break;
        }
        return $block;
    }

    public function getLookingBehind(): Block{
        $block = Block::get(Block::AIR);
        switch($this->getDirection()){
            case 0:
                $block = $this->getLevel()->getBlock($this->add(-1, 0, 0));
                break;
            case 1:
                $block = $this->getLevel()->getBlock($this->add(0, 0, -1));
                break;
            case 2:
                $block = $this->getLevel()->getBlock($this->add(1, 0, 0));
                break;
            case 3:
                $block = $this->getLevel()->getBlock($this->add(0, 0, 1));
                break;
        }
        return $block;
    }

    public function breakBlock(Block $block): void{
        $inv = $this->getMineInv();
        $id = $block->getId();
        if(in_array($id, $this->ore) and $this->is_ore){
            $id = $this->ores[$id];
        }
        $item = Item::get($id, $block->getDamage());
        if(!empty($inv)){
            if(!$inv->canAddItem($item) and $this->is_sell){
                 Main::get()->sell($this->getOwner(), $inv);
            }
            if($this->isFortune($this->getInventory()->getItemInHand())){

            }else $inv->addItem($item);
            
        }
        $this->getLevel()->setBlock($block, Block::get(Block::AIR), true, true);
    }

    public function getOwner():?Player{
        $player = Server::getInstance()->getPlayer($this->player);
        return $player;
    }

    private function increaseDrops(array $drops, int $amount = 1) {
        $newDrops = [];
        foreach($drops as $drop){
            $newDrops[] = $drop->setCount(1 + $amount);
        }
        return $newDrops;
    }

    public function isFortune($item) :bool{
        $inv = $this->getMineInv();
        $block = $this->getLookingBlock();
        $item = $this->getInventory()->getItemInHand();
        if(($fortuneEnchantment = $item->getEnchantment(Enchantment::FORTUNE)) instanceof EnchantmentInstance){
            $level = $fortuneEnchantment->getLevel() + 1;
            $rand = rand(1, $level);
            $drops = [];
            if($item instanceof TieredTool){
                switch($block->getId()){
                    case Block::COAL_ORE:
                        if($item instanceof Pickaxe){
                            $drops[] = $this->increaseDrops($block->getDrops($item), $rand);
                        }
                        break;
                    case Block::LAPIS_ORE:
                        if($item instanceof Pickaxe && $item->getTier() > TieredTool::TIER_WOODEN){
                            $drops[] = $this->increaseDrops($block->getDrops($item), rand(0, 4) + $rand);
                        }
                        break;
                    case Block::GLOWING_REDSTONE_ORE:
                    case Block::REDSTONE_ORE:
                        if($item instanceof Pickaxe && $item->getTier() > TieredTool::TIER_WOODEN){
                            $drops[] = $this->increaseDrops($block->getDrops($item), rand(1, 2) + $rand);
                        }
                        break;
                    case Block::NETHER_QUARTZ_ORE:
                        if($item instanceof Pickaxe && $item->getTier() > TieredTool::TIER_WOODEN){
                            $drops[] = $this->increaseDrops($block->getDrops($item), rand(0, 1) + $rand);
                        }
                        break;
                    case Block::DIAMOND_ORE:
                        if($item instanceof Pickaxe && $item->getTier() >= TieredTool::TIER_IRON){
                            $drops[] = $this->increaseDrops($block->getDrops($item), $rand);
                        }
                        break;
                    case Block::EMERALD_ORE:
                        if($item instanceof Pickaxe && $item->getTier() >= TieredTool::TIER_IRON){
                           $drops[] = $this->increaseDrops($block->getDrops($item), $rand);
                        }
                        break;
                    case Block::CARROT_BLOCK:
                    case Block::POTATO_BLOCK:
                    case Block::BEETROOT_BLOCK:
                    case Block::WHEAT_BLOCK:
                        if($item instanceof Axe || $item instanceof Pickaxe){
                            if($block->getDamage() >= 7){
                                $drops[] = $this->increaseDrops($block->getDrops($item), rand(1, 3) + $rand);
                            }
                        }
                        break;
                    case Block::MELON_BLOCK:
                        if($item instanceof Axe || $item instanceof Pickaxe){
                           $drops[] = $this->increaseDrops($block->getDrops($item), rand(3, 9) + $rand);
                        }
                        break;
                    case Block::LEAVES:
                        if(rand(1, 100) <= 10 + $level * 2){
                           $inv->addItem(Item::get(Item::APPLE, 0, 1));
                        }
                        break;
                        default:
                        unset($drops);
                        $drops = Item::get(1,0);
                        break;
                }
            }
            if(is_array($drops)){
                foreach($drops as $items){
                    if(is_array($items)){
                        foreach($items as $item){
                         $inv->addItem($item);   
                        }  
                    }else $inv->addItem($items);
                }
            }else return false;
            return true;
        }
        return false;
    }
}
