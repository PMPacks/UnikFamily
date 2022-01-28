<?php
namespace phuongaz\AuraBoss\Entity;

use pocketmine\entity\Human;
use phuongaz\AuraBoss\Boss;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\block\spawn;
use pocketmine\utils\TextFormat;
use libs\Utils;
class ViThu extends BossMain {

	public $target;
	public $scale = 1;
	public $attackDamage = 3;
	public $health;
	public $drops = [];
	public $name;
	public $speed = 0.5;

	public $spawnPos;

	private $range = 500;
	
	public function __construct(Level $level, CompoundTag $nbt){
		if(is_null($nbt)){
			$nbt = Entity::createBaseNBT($player);
    	}
    	$skindata = $nbt->getString("skin", "");
		$skinget = Boss::getInstance()->getManager()->getSkin($skindata);
    	$this->setSkin($skinget);
    	$this->name = $nbt->getString("CustomName", "");
    	$this->scale = $nbt->getFloat("scale", 1);
    	$this->health = $nbt->getFloat("Health", 20);
    	$this->attackDamage = $nbt->getFloat("damage", 3);
    	$drops = $nbt->getString("drops","");
		if($drops !== ""){
			foreach(explode(' ',$drops) as $item){
				$item = explode(';',$item);
				$this->drops[] = [Item::get($item[0],$item[1] ?? 0,$item[2] ?? 1,$item[3] ?? ""),$item[4] ?? 100];
			}
		}
        $this->attackWait = 10;
        $this->regenerationRate = 15;
		parent::__construct($level, $nbt);
	}

    public function initEntity(): void{
        parent::initEntity();
        $this->setMaxHealth($this->health);
        $this->setHealth($this->health);
      	$this->setNameTag($this->name);
        $this->setNameTagAlwaysVisible(true);
        $this->setScale($this->scale);
	}

	public function getProgress(int $progress, int $size): string {
		$divide = $size > 750 ? 50 : ($size > 500 ? 20 : ($size > 300 ? 15 : ($size > 200 ? 10 : ($size > 100 ? 5 : 3)))); 
		$percentage = number_format(($progress / $size) * 100, 2);
		$progress = (int) ceil($progress / $divide);
		$size = (int) ceil($size / $divide);

		return TextFormat::BOLD.  TextFormat::GRAY . "§c♥ " . TextFormat::GREEN . str_repeat("|", $progress) .
			TextFormat::RED . str_repeat("|", $size - $progress) . "".
			TextFormat::AQUA . "{$percentage} %";
	}

	public function setSpeed($int) {
		$this->speed = $int;
	}

	public function onUpdate(int $currentTick): bool {

		$this->setNameTag($this->name. "\n". $this->getProgress((int)$this->getHealth(), (int)$this->getMaxHealth()));
		parent::onUpdate($currentTick);
		return !$this->closed;
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

	public function kill():void {
		$this->setNameTag($this->name);
		parent::kill();
	}

	public function close(): void{
		parent::close();
		//$this->plugin = null;
	}
}