<?php

namespace ShinPickaxeLevel\task;


use ShinPickaxeLevel\form\TopForm;
use pocketmine\form\Form;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class SortTask extends AsyncTask
{
    private $receiver, $moneyData, $addOp, $page, $ops, $banList, $closeForm;
    private $max = 0;
    private $topList;

    public function __construct($receiver, array $moneyData, bool $addOp, int $page, array $ops, array $banList, Form $closeForm = null)
    {
        $this->receiver = $receiver;
        $this->moneyData = $moneyData;
        $this->addOp = $addOp;
        $this->page = $page;
        $this->ops = $ops;
        $this->banList = $banList;
        $this->closeForm = serialize($closeForm);
    }

    public function onRun() : void
    {
        $this->topList = serialize((array)$this->getTopList());
    }

    private function getTopList()
    {
		
        $money = (array)$this->moneyData;
        $banList = (array)$this->banList;
        $ops = (array)$this->ops;
        arsort($money);
        $ret = [];
        $n = 1;
        $this->max = ceil((count($money) - count($banList) - ($this->addOp ? 0 : count($ops))) / 5);
        $this->page = (int)min($this->max, max(1, $this->page));
        foreach ($money as $p => $m) {
            $p = strtolower($p);
            if (isset($banList[$p])) continue;
            if (isset($this->ops[$p]) and $this->addOp === false) continue;
            $current = (int)ceil($n / 5);
            if ($current === $this->page) {
                $ret[$n] = [$p, $m];
            } elseif ($current > $this->page) {
                break;
            }
            ++$n;
        }
        return $ret;
    }

    public function onCompletion(Server $server) : void
    {
        $output = "";
        $message = "§l§c§l● §6[§bHạng§a %1§6] §c%2:§a %3 §dCấp\n";
        foreach (unserialize($this->topList) as $n => $list) {
            $output .= str_replace(["%1", "%2", "%3"], [$n, $list[0], $list[1]], $message);
        }
        $output = trim($output);
        if ($this->receiver === "CONSOLE") {
            $output = "§l§c●§6 Xếp Hạng Cấp Cúp§d ($this->page of $this->max)\n" . $output;
            Server::getInstance()->getLogger()->notice($output);
        } else {
            if (($player = Server::getInstance()->getPlayerExact($this->receiver)) instanceof Player) {
                $closeForm = unserialize($this->closeForm);
                if (!($closeForm instanceof Form)) $closeForm = null;
                $player->sendForm(new TopForm($player, $this->page, $this->max, explode("\n", $output), $closeForm));
            }
        }
    }
}