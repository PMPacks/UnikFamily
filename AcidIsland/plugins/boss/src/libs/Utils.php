<?php

declare(strict_types = 1);

namespace libs;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Skin;
use pocketmine\Player;
use TypeError;

class Utils {

    const N = 'N';

    const NE = '/';

    const E = 'E';

    const SE = '\\';

    const S = 'S';

    const SW = '/';

    const W = 'W';

    const NW = '\\';

    const HEX_SYMBOL = "e29688";

    /**
     * @param int $degrees
     *
     * @return string|null
     */
    public static function getCompassPointForDirection(int $degrees): ?string {
        $degrees = ($degrees - 180) % 360;
        if($degrees < 0) {
            $degrees += 360;
        }
        if(0 <= $degrees and $degrees < 22.5) {
            return self::N;
        }
        if(22.5 <= $degrees and $degrees < 67.5) {
            return self::NE;
        }
        if(67.5 <= $degrees and $degrees < 112.5) {
            return self::E;
        }
        if(112.5 <= $degrees and $degrees < 157.5) {
            return self::SE;
        }
        if(157.5 <= $degrees and $degrees < 202.5) {
            return self::S;
        }
        if(202.5 <= $degrees and $degrees < 247.5) {
            return self::SW;
        }
        if(247.5 <= $degrees and $degrees < 292.5) {
            return self::W;
        }
        if(292.5 <= $degrees and $degrees < 337.5) {
            return self::NW;
        }
        if(337.5 <= $degrees and $degrees < 360.0) {
            return self::N;
        }
        return null;
    }

    /***
     * @param string $text
     * @param int $length
     * @param int $place
     *
     * @return string
     */
    public static function scrollText(string $text, int $length, int $place): string {
        $textLength = strlen($text);
        if($place > $textLength) {
            $place = $place % $textLength;
        }
        return substr($text, $place, $length);
    }

    /**
     * @param float $deg
     *
     * @return string
     */
    public static function getCompassDirection(float $deg) : string {
        $deg %= 360;
        if($deg < 0) {
            $deg += 360;
        }
        if(22.5 <= $deg and $deg < 67.5) {
            return "NW";
        }
        elseif(67.5 <= $deg and $deg < 112.5) {
            return "N";
        }
        elseif(112.5 <= $deg and $deg < 157.5) {
            return "NE";
        }
        elseif(157.5 <= $deg and $deg < 202.5) {
            return "E";
        }
        elseif(202.5 <= $deg and $deg < 247.5) {
            return "SW";
        }
        elseif(247.5 <= $deg and $deg < 292.5) {
            return "S";
        }
        elseif(292.5 <= $deg and $deg < 337.5) {
            return "SW";
        }
        else {
            return "W";
        }
    }

    /**
     * @return string
     */
    public static function getMapBlock(): string {
        return hex2bin(self::HEX_SYMBOL);
    }
    /**
     * @param int $degrees
     * @param string $colorActive
     * @param string $colorDefault
     *
     * @return array
     */
    public static function getASCIICompass(int $degrees, string $colorActive, string $colorDefault): array {
        $ret = [];
        $point = self::getCompassPointForDirection($degrees);
        $row = "";
        $row .= ($point === self::NW ? $colorActive : $colorDefault) . self::NW;
        $row .= ($point === self::N ? $colorActive : $colorDefault) . self::N;
        $row .= ($point === self::NE ? $colorActive : $colorDefault) . self::NE;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::W ? $colorActive : $colorDefault) . self::W;
        $row .= $colorDefault . "+";
        $row .= ($point === self::E ? $colorActive : $colorDefault) . self::E;
        $ret[] = $row;
        $row = "";
        $row .= ($point === self::SW ? $colorActive : $colorDefault) . self::SW;
        $row .= ($point === self::S ? $colorActive : $colorDefault) . self::S;
        $row .= ($point === self::SE ? $colorActive : $colorDefault) . self::SE;
        $ret[] = $row;
        return $ret;
    }

    /**
     * Check if classes in an array are a block of class
     *
     * @param array $array
     * @param string $class
     *
     * @return bool
     *
     * @throws TypeError
     */
    public static function validateObjectArray(array $array, string $class): bool {
        foreach($array as $key => $item) {
            if(!($item instanceof $class)) {
                throw new TypeError("Element \"$key\" is not an instance of $class");
            }
        }
        return true;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getSkinDataFromPNG(string $path): string {
        $image = imagecreatefrompng($path);
        $data = "";
        for($y = 0, $height = imagesy($image); $y < $height; $y++) {
            for($x = 0, $width = imagesx($image); $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $data .= pack("c", ($color >> 16) & 0xFF)
                    . pack("c", ($color >> 8) & 0xFF)
                    . pack("c", $color & 0xFF)
                    . pack("c", 255 - (($color & 0x7F000000) >> 23));
            }
        }
        return $data;
    }

    /**
     * @param string $skinData
     *
     * @return Skin
     */
    public static function createSkin(string $skinData) {
        return new Skin("Standard_Custom", $skinData, "", "geometry.humanoid.custom");
    }

    /**
     * @param string $message
     *
     * @return int
     */
    public static function colorCount(string $message): int {
        $colors = "abcdef0123456789lo";
        $count = 0;
        for($i = 0; $i < strlen($colors); $i++) {
            $count += substr_count($message, "§" . $colors{$i});
        }
        return $count;
    }

    /**
     * @param Player $player
     * @param int $id
     * @param int $seconds
     * @param int $amp
     */
    public static function addEffect(Player $player, int $id, int $seconds, int $amp = 1): void{
        $player->addEffect(new EffectInstance(Effect::getEffect($id), 20 * $seconds, $amp));
    }

    /** @var BigEndianNBTStream */
    private static $serializer;

    public static function init() : void{
        self::$serializer = new BigEndianNBTStream();
    }

    public static function serialize(Item $item) : string{
        return self::$serializer->writeCompressed($item->nbtSerialize());
    }

    public static function unserialize(string $string) : Item{
        /** @noinspection PhpParamsInspection */
        return Item::nbtDeserialize(self::$serializer->readCompressed($string));
    }
    
}