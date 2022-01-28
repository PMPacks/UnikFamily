<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝



namespace BaliGamerz\SkyBlock;

use pocketmine\scheduler\Task;

class PluginHearbeat extends Task
{

    /** @var int */
    private $titleIndex = 0;
    private $timeToSave = 10 * 1200;

    /**
     * PluginHeartbeat constructor.
     *
     * @param Main $owner
     */
    private $main;


    public function __construct(Main $owner)
    {
        $this->main = $owner;
    }

    /**
     * @return Main
     */
    public function getMain(): Main
    {
        return $this->main;
    }

    public function onRun($currentTick)
    {
            if ($this->main->config['scorehud']) {
                $title = $this->main->config['titles'];
                if (!isset($title[$this->titleIndex])) {
                    $this->titleIndex = 0;
                }
                foreach ($this->main->getServer()->getOnlinePlayers() as $player) {
                    $this->main->addScore($player, $title[$this->titleIndex], $this->main->config['lines']);
                }
                $this->titleIndex++;
            }

        $this->timeToSave--;
        if ($this->timeToSave <= 1) {
            $this->main->getIslandManager()->updateDisableIslandServer();
            $this->timeToSave = 10 * 1200;
        }
    }
}