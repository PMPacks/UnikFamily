<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;

class Utils
{


    public function getPlugin(): Main
    {
        return Main::getInstance();
    }

    /**
     * Return minutes
     *
     * @param $seconds
     * @return string
     */
    public static function printSeconds($seconds): string
    {
        $m = floor($seconds / 60);
        $s = floor($seconds % 60);
        return (($m < 10 ? "0" : "") . $m . ":" . ($s < 10 ? "0" : "") . (string)$s);
    }

    public static function addSound(Player $player, int $volume = 2, $pitch = 1, string $sound_name = "note.bell")
    {
        $sound = new PlaySoundPacket();
        $sound->soundName = $sound_name;
        $sound->x = $player->getX();
        $sound->y = $player->getY();
        $sound->z = $player->getZ();
        $sound->volume = $volume;
        $sound->pitch = $pitch;
        $player->dataPacket($sound);
    }


    /**
     * @return array
     */
    public static function getFormatPlayerDataPath(): array
    {
        return [
            "island" => "",
            "mana" => 0,
            "last-creation" => 0,
            'queueIndexTop' => ['level' => 0, 'size' => 0, 'mine' => 0, 'money' => 0],
            "objectives" =>
                [
                    "success" => [],
                    "running" => 0,
                    "objectiveData" => []
                ]];
    }

    /**
     * @param string $delimeter
     * @param string $string
     * @param float $yaw
     * @param float $pitch
     * @return Vector3
     */
    public static function stringToVector(string $delimeter, string $string, &$yaw = 0.0, &$pitch = 0.0): Vector3
    {
        $split = explode($delimeter, $string);
        if (isset($split[3]) && isset($split[4])) {
            $yaw = floatval($split[3]);
            $pitch = floatval($split[4]);
        }
        return new Vector3(intval($split[0]), intval($split[1]), intval($split[2]));
    }

    /**
     * @param string $delimeter
     * @param Vector3 $vector
     * @return string
     */
    public static function vectorToString(string $delimeter, Vector3 $vector, $yaw = 0.0, $pitch = 0.0): string
    {
        $array = [$vector->getX(), $vector->getY(), $vector->getZ()];
        if ($yaw > 0 && $pitch > 0) {
            $array[] = $yaw;
            $array[] = $pitch;
        }
        $string = "";
        foreach ($array as $splitValue) {
            $string .= $splitValue . ":";
        }
        return $string;
    }

    public static function extractZipFile($name)
    {
        if (!(is_dir(Main::getInstance()->getDataFolder() . $name))) {
            if (file_exists(Main::getInstance()->getDataFolder() . "{$name}.zip")) {
                $zip = new \ZipArchive;
                $zip->open(Main::getInstance()->getDataFolder() . "{$name}.zip");
                $zip->extractTo(Main::getInstance()->getDataFolder() . $name);
                $zip->close();
                unset($zip);
            }
        }
    }

    public static function getProgress($total): string
    {
        $current = $total / 10.5;
        return "§b".str_repeat("■", round($current, 0)) .
            "§7". str_repeat("■", round(10 - $current, 0));
    }

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
     * Return island path
     *
     * @param $id
     * @return string
     */
    public static function getIslandPath($id): string
    {
        return Main::getInstance()->getDataFolder() . "islands" . DIRECTORY_SEPARATOR . $id . ".json";
    }

    /**
     * Return an unique island id
     *
     * @return string
     *
     */
    public static function genIslandId(): string
    {
        return "a" . floor(microtime(true)) . "-" . rand(1, 9999);
    }

    /**
     * The inverse of parse a position
     *
     * @param Position $position
     * @return string
     */
    public static function createPositionString(Position $position): string
    {
        return "{$position->getLevel()->getName()},{$position->getX()},{$position->getY()},{$position->getZ()}";
    }

    /**
     * Return a parsed position
     *
     * @param $string
     * @return null|Position
     */
    public static function parsePosition($string): ?Position
    {
        $array = explode(",", $string);
        if (isset($array[3])) {
            $level = Main::getInstance()->getServer()->getLevelByName($array[0]);
            if ($level instanceof Level) {
                return new Position($array[1], $array[2], $array[3], $level);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }


    public static function translateSize($c): string
    {
        if ($c < 1) {
            $id = "EXTRA_SMALL";
        }

        if ($c >= Main::getInstance()->config["CategoryByBlocks"]["XS"]) {
            $id = "EXTRA_SMALL";
        }
        if ($c >= Main::getInstance()->config["CategoryByBlocks"]["S"]) {
            $id = "SMALL";
        }
        if ($c >= Main::getInstance()->config["CategoryByBlocks"]["M"]) {
            $id = "MEDIUM";
        }
        if ($c >= Main::getInstance()->config["CategoryByBlocks"]["L"]) {
            $id = "LARGE";
        }
        if ($c >= Main::getInstance()->config["CategoryByBlocks"]["XL"]) {
            $id = "EXTRA_LARGE";
        }
        if ($c > -1 and $c > 0 and $c < Main::getInstance()->config["CategoryByBlocks"]["S"]) {
            $id = "EXTRA SMALL";
        }
        return $id;
    }

}