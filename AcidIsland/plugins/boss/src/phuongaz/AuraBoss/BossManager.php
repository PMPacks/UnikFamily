<?php

namespace phuongaz\AuraBoss;

use pocketmine\entity\Skin;
use pocketmine\utils\TextFormat;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\scheduler\Task;

Class BossManager {

	public function getPlugin(){
		return Boss::getInstance();
	}

    public function execCommand($int, $player){
		$data = $this->getPlugin()->getBossData($int);
		if($data !== null){
			//var_dump($data);
			foreach($data["commands"] as $command){
				//var_dump($command);
				$command = str_replace("{player}", strtolower($player->getName()), $command);
				$this->getPlugin()->getServer()->dispatchCommand(New ConsoleCommandSender(), $command);
			}
		}
	}

    public function fixSkin(string $filename){
        $path = $this->getPlugin()->getDataFolder()."$filename.png";
        $img = $this->convertSize($path, 64, 64);
        imagepng($img, $this->getPlugin()->getDataFolder()."$filename.png");
    }

   public function convertSize($file, $w, $h, $crop=FALSE) {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width-($width*abs($r-$w/$h)));
            } else {
                $height = ceil($height-($height*abs($r-$w/$h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w/$h > $r) {
                $newwidth = $h*$r;
                $newheight = $h;
            } else {
                $newheight = $w/$r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
        return $dst;
    }

	public function getSkin(string $filename){
        if(!file_exists($this->getPlugin()->getDataFolder().$filename.".png")) return null;
        $img = $this->convertSize($this->getPlugin()->getDataFolder().$filename.".png", 64, 64);
        imagepng($img, $this->getPlugin()->getDataFolder().$filename.".png");
        $path = $this->getPlugin()->getDataFolder(). $filename.".png";
        if(!is_file($path)){
            return null;
        }
        $img = @imagecreatefrompng($path);
        $bytes = '';
        $l = (int) @getimagesize($path)[1];
        for ($y = 0; $y < $l; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return new Skin("Standard_CustomSlim", $bytes, "", "geometry.".$filename, file_get_contents($this->getPlugin()->getDataFolder(). $filename .".json"));
	}

	public function spawnCountDown($id, $time = 10){
		$this->getPlugin()->getScheduler()->scheduleRepeatingTask(new class($this->getPlugin(), $time, $id) extends Task{
			private $time = 5;
			private $id;
			public function __construct($main, $time, $id){
				$this->main = $main;
				$this->time = $time;
				$this->id = $id;
			}

			public function onRun(int $currentTick): void{
				--$this->time;
				if($this->time == 0){
					$id = $this->id;
					$this->main->spawnBoss($id);
					$this->getHandler()->cancel();
				}
			}
		},20);
	}
}