<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\SkyBlockManager;

use BaliGamerz\SkyBlock\Entity\NpcClass;
use BaliGamerz\SkyBlock\FunctionLogic\ListFunction;
use BaliGamerz\SkyBlock\Utils;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\entity\Skin;
use BaliGamerz\SkyBlock\Main;
use pocketmine\utils\TextFormat;

class SkyBlockManager
{

    /** @var Main */
    private $plugin;

    /**
     * SkyBlockBlockManager constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     * @param $isname
     * @param array $dataThema
     */
    public function generateIsland(Player $player, $isname, array $dataThema)
    {
        $start = microtime(true);
        $type = $dataThema['name'];
        $this->plugin->getIslandManager()->createIsland($player, $isname, $type, $dataThema['spawn']);
        $island = $this->getPlayerConfig($player)["island"];
        $this->levelMake($island, $dataThema, $start, $player);
    }

    /**
     * @param $sender
     * @param $pngName
     * @param $geometryName
     * @return CompoundTag
     */
    public function getSkinTag($sender, string $pngName, string $geometryName): CompoundTag
    {
        $path = $this->plugin->getDataFolder() . "Skin/" . $pngName . ".png";
        $img = @imagecreatefrompng($path);
        $skinbytes = "";
        $s = @getimagesize($path);
        for ($y = 0; $y < $s[1]; $y++) {
            for ($x = 0; $x < $s[0]; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        $skinTag = new CompoundTag("Skin", [
            "Name" => new StringTag("Name", $sender->getSkin()->getSkinId()),
            "Data" => new ByteArrayTag("Data", $skinbytes),
            "GeometryName" => new StringTag("GeometryName", "geometry." . $pngName),
            "GeometryData" => new ByteArrayTag("GeometryData", file_get_contents($this->plugin->getDataFolder() . "Skin/" . $geometryName . ".json"))
        ]);
        return $skinTag;
    }


    /**
     * @param Player $sender
     * @param $position
     * @param Level $level
     * @param $timeleft
     */
    public function createNpc(Player $sender, $position, Level $level, $timeleft)
    {
        $dataSkin = $this->plugin->dataThemas['npc'];
        $name = $dataSkin['name'];
        $posPlus = $dataSkin['position'];
        $nbt = Entity::createBaseNBT(new Vector3($position[0], $position[1] + $posPlus, $position[2]));
        $nbt->setTag($this->getSkinTag($sender, $dataSkin['index_data'], $dataSkin['index_data']));
        $nbt->setShort("Health", 1);
        $nbt->setString("MenuName", "");
        $nbt->setString("CustomName", $name);
        $nbt->setFloat("damageEntity", 1.1);
        $sender->saveNBT();
        $human = Entity::createEntity("NpcClass", $level, $nbt);
        $human->spawnToAll();
        Main::sendMessage($sender, "§a§lTHÀNH CÔNG!. §r§aĐang Tạo Đảo Của Bạn Còn §f" . round(microtime(true) - $timeleft, 3) . "s");
    }

    /**
     * @param $identifier
     * @param $dataThema
     * @param $timeleft
     * @param $sender
     * @return bool
     */
    public function levelMake($identifier, $dataThema, $timeleft, $sender): bool
    {
        $sb = $this->plugin;
        $dir = $dataThema['dirname'];
        $tema = $dataThema['name'];
        $npc = explode(":", $dataThema['npcPosition']);
        $sign = explode(":", $dataThema['signPosition']);
        $spawn = explode(":", $dataThema['spawn']);
        Main::sendMessage($sender, "§e§lRUNNING!. §r§8Preparing a world");
        if (!(is_dir($sb->getServer()->getDataPath() . "worlds/" . $identifier))) {
            $fromFolderName = $dir;
            $toFolderName = $identifier;
            $fromPath = $sb->getDataFolder() . $fromFolderName;
            $toPath = $sb->getServer()->getDataPath() . "worlds/" . $toFolderName;
            self::copy($fromPath, $toPath);
            if (!($levelDatContent = file_get_contents($toPath . "/level.dat"))) {
                return true;
            }
            $nbt = new BigEndianNBTStream();
            $levelData = $nbt->readCompressed($levelDatContent);
            if (!($levelData instanceof CompoundTag) or !$levelData->hasTag("Data", CompoundTag::class)) {

                return true;
            }
            $dataWorkingWith = $levelData->getCompoundTag("Data");

            if (!$dataWorkingWith->hasTag("LevelName", StringTag::class)) {


                return true;
            }
            $dataWorkingWith->setString("LevelName", $toFolderName);

            if (!(file_put_contents(
                $toPath . "/level.dat",
                $nbt->writeCompressed(new CompoundTag("", array($dataWorkingWith)))
            ))) {
                return true;
            }
            $server = $sb->getServer();
            $server->loadLevel($identifier);

            $level = $server->getLevelByName($identifier);
            $level->setSpawnLocation(new Vector3((int)$spawn[0], (int)$spawn[1], (int)$spawn[2]));
            $tile = $level->getTile(new Vector3((int)$sign[0], (int)$sign[1], (int)$sign[2]));
            if ($tile instanceof Sign) {
                $arrayTitle = implode(":", $this->plugin->config['island']['sign']);
                $arrayTitle = str_replace(["{player}", "{island}", "{type}"], [$sender->getName(), $identifier, $tema], $arrayTitle);
                $arrayTitle = explode(":", $arrayTitle);
                $tile->setText($arrayTitle[0] ?? "   ", $arrayTitle[1] ?? "   ", $arrayTitle[2] ?? "   ", $arrayTitle[3] ?? "   ");
            }
            Main::sendMessage($sender, "§e§lRUNNING!. §r§8Created a npc island");
            $npcPos = [(int)$npc[0], (int)$npc[1], (int)$npc[2]];
            $this->createNpc($sender, $npcPos, $level, $timeleft);
        }
        return true;
    }

    /**
     * @param string $from
     * @param string $to
     */
    public static function copy(string $from, string $to): void
    {
        if (is_dir($from)) {
            $objects = scandir($from);

            mkdir($to);

            foreach ($objects as $object) {
                if ($object !== "." and $object !== "..") {
                    if (is_dir($from . "/" . $object)) {
                        self::copy($from . "/" . $object, $to . "/" . $object);
                    } else {
                        copy($from . "/" . $object, $to . "/" . $object);
                    }
                }
            }
        }
    }


    /**
     * Register a user
     *
     * @param Player $player
     */
    public function registerUser(Player $player)
    {
        $this->plugin->playerDataPath[strtolower($player->getName())] = Utils::getFormatPlayerDataPath();
    }

    /**
     * @param Player $player
     * @param $name
     */
    public function setIslandUser(Player $player, $name)
    {
        $this->plugin->playerDataPath[strtolower($player->getName())]["island"] = $name;
    }

    /**
     * Tries to register a player
     *
     * @param Player $player
     */
    public function tryRegisterUser(Player $player)
    {
        if (!isset($this->plugin->playerDataPath[strtolower($player->getName())])) {
            $this->registerUser($player);
        }
    }

    /**
     * Return player config
     *
     * @param Player $player
     * @return array
     */
    public function getPlayerConfig(Player $player): array
    {
        return $this->plugin->playerDataPath[strtolower($player->getName())];
    }

}