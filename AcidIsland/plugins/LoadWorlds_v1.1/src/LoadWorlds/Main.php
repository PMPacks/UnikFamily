<?php

namespace LoadWorlds;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;


class Main extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds/"), [".", ".."]) as $file) {
            if ($this->getServer()->isLevelGenerated($file) && !$this->getServer()->isLevelLoaded($file)) {
                $this->getServer()->loadLevel($file);
            }
        }
    }


}
