<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝




namespace BaliGamerz\SkyBlock\Entity;


use BaliGamerz\SkyBlock\Main;
use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket as SetEntityDataPacket;
use pocketmine\Player;

trait NpcTrait
{
    /** @var CompoundTag */
    public $namedtag;

    /**
     * @return DataPropertyManager
     */
    abstract public function getDataPropertyManager(): DataPropertyManager;

    /**
     * @return string
     */
    abstract public function getNameTag(): string;

    abstract public function sendNameTag(Player $player): void;

    abstract public function setGenericFlag(int $flag, bool $value = true): void;

    public function prepareMetadata(): void
    {
        $this->setGenericFlag(Entity::DATA_FLAG_IMMOBILE, true);
        if (!$this->namedtag->hasTag("Scale", FloatTag::class)) {
            $this->namedtag->setFloat("Scale", 1.0, true);
        }
        if ($this->namedtag->hasTag("height")) {
            $this->getDataPropertyManager()->setFloat(Entity::DATA_BOUNDING_BOX_HEIGHT, $this->namedtag->getFloat("height"));
        }
        $this->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, $this->namedtag->getFloat("Scale"));

    }

    protected function tryChangeMovement(): void
    {
    }

    public function sendData($playerList, array $data = null): void
    {
        if (!is_array($playerList)) {
            $playerList = [$playerList];
        }
        foreach ($playerList as $p) {
            $playerData = $data ?? $this->getDataPropertyManager()->getAll();
            unset($playerData[self::DATA_NAMETAG]);
            $pk = new SetEntityDataPacket();
            $pk->entityRuntimeId = $this->getId();
            $pk->metadata = $playerData;
            $p->dataPacket($pk);
            $this->sendNameTag($p);
        }
    }

    public function saveSkyBlockNbt(): void
    {
        $visibility = 0;
        if ($this->isNameTagVisible()) {
            $visibility = 1;
            if ($this->isNameTagAlwaysVisible()) {
                $visibility = 2;
            }
        }
        $scale = $this->getDataPropertyManager()->getFloat(Entity::DATA_SCALE);
        $this->namedtag->setInt("NameVisibility", $visibility, true);
        $this->namedtag->setFloat("Scale", $scale, true);
    }

    public function getDisplayName(Player $player): string
    {
        $main = Main::getInstance();
        $name = $this->getName();

        switch ($name) {
            case "{level_top}":
                $vars = [
                    "&" => "§",
                    "{level_top}" => $main->getFormatIslandTop($player, 'level'),
                ];
                return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
            case "{size_top}":
                $vars = [
                    "&" => "§",
                    "{size_top}" => $main->getFormatIslandTop($player, 'size'),
                ];
                return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
            case "{mine_top}":
                $vars = [
                    "&" => "§",
                    "{mine_top}" => $main->getFormatIslandTop($player, 'mine'),
                ];
                return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
            case "{money_top}":
                $vars = [
                    "&" => "§",
                    "{money_top}" => $main->getFormatIslandTop($player, 'money')
                ];
                return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
            default:
                $vars = [
                    "{name}" => $player->getName(),
                    "{progress}" => $main->getProgress($player),
                    "{progress_size}" => $main->getProgressSize($player),
                    "{progress_int}" => $main->getIntProgress($player),
                    "{level}" => $main->getLevel($player),
                    "{mine}" => $main->getMine($player),
                    "{size}" => $main->getTranslateSize($player),
                    "{sky_name}" => $main->getIslandName($player),
                    "{sky_member_online}" => $main->getMemberOnline($player),
                    "{sky_allMember}" => $main->getAllMember($player),
                    "{balance}" => number_format($main->getIslandMoney($player)),
                    "{task}" => $main->getPlayerCompletedQuest($player),
                    "&" => "§"
                ];
                return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
        }
    }
}