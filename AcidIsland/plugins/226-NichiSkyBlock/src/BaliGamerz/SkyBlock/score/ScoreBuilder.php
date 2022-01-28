<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝



namespace BaliGamerz\SkyBlock\score;


use _64FF00\PurePerms\PurePerms;
use onebone\coinapi\CoinAPI;
use BaliGamerz\SkyBlock\Main;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\Player;

class ScoreBuilder
{


    /**
     * @var Main
     */
    private $plugin;


    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }


    /**
     * @param Player $player
     * @param string $title
     * @param array $lines
     */
    public function build(Player $player, string $title, array $lines)
    {
        $pks = [];
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = "SB";
        $pks[] = $pk;

        $pk = new SetDisplayObjectivePacket();
        $pk->objectiveName = "SB";
        $pk->criteriaName = "dummy";
        $pk->displaySlot = "sidebar";
        $pk->displayName = $title;
        $pk->sortOrder = 0;
        $pks[] = $pk;

        $pk = new SetScorePacket();
        $i = 0;
        $pk->type = 0;
        foreach ($lines as $line) {
            $pk->entries[] = new ScorePacketEntryCustom("SB", $this->translate($player, $line), $i++);
        }
        $pks[] = $pk;
        foreach ($pks as $packet){
            $player->sendDataPacket($packet);
        }
    }



    /**
     * Removes a scoreboard from the player specified.
     *
     * @param Player $player
     */
    public function unBuild(Player $player): void{
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = "SB";
        $player->sendDataPacket($pk);
    }

    /**
     * @param $number
     * @return string
     */
    public function shortNumber($number, $per = 0): string
    {
        $result = is_numeric($number) ? $number : 0;
        $integer = (int)$result;

        $key = [
            12 => 'T',
            9 => 'B',
            6 => 'M',
            3 => 'K',
            0 => ''
        ];
        foreach ($key as $exponent => $abbrev){
            if(abs($integer) >= pow(10, $exponent)){
                $display = $integer / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals).$abbrev;
                break;
            }
        }
        return $number;

    }



    /**
     * @param Player $player
     * @param string $message
     * @return string
     */
    public function translate(Player $player, string $message): string
    {
        $message = str_replace([
            "{time}",
            "{date}",
            "&",
            "{sky_progress_int}",
            "{sky_progress_size}",
            "{objective}",
            "{objective_progress}",
            "{size_int}",
            "{sky_mine}",
            "{sky_level}",
            "{sky_progress}",
            "{sky_member_online}",
            "{sky_allMember}",
            "{sky_name}",
            "{health}",
            "{max_health}",
            "{name}",
            "{money}",
            "{online}",
            "{max_online}",
            "{rank}",
            "{gem}",
            "{point}",
            "{chuyensinh}",
            "{item_name}",
            "{item_id}",
            "{item_meta}",
            "{item_count}",
            "{x}",
            "{y}",
            "{z}",
            "{faction}",
            "{faction_power}",
            "{load}",
            "{tps}",
            "{level_name}",
            "{level_folder_name}",
            "{ip}",
            "{ping}",
            "{sky_owner}"
        ], [
            date($this->plugin->config["time-format"]),
            date($this->plugin->config["date-format"]),
            "§",
            $this->plugin->getIntProgress($player),
            $this->plugin->getProgressSize($player),
            $this->plugin->getPlayerObjective($player),
            $this->plugin->getPlayerObjectiveByProgress($player),
            $this->plugin->getIslandSize($player),
            $this->plugin->getMine($player),
            $this->plugin->getLevel($player),
            $this->plugin->getProgress($player),
            $this->plugin->getMemberOnline($player),
            $this->plugin->getAllMember($player),
            $this->plugin->getIslandName($player),
            round($player->getHealth()),
            $player->getMaxHealth(),
            $player->getName(),
            $this->shortNumber($this->getPlayerMoney($player)),
            count($this->plugin->getServer()->getOnlinePlayers()),
            $this->plugin->getServer()->getMaxPlayers(),
            $this->getPlayerRank($player),
            $this->getGem($player),
            $this->getPlayerCoin($player),
            $this->getPlayerCS($player),
            $player->getInventory()->getItemInHand()->getName(),
            $player->getInventory()->getItemInHand()->getId(),
            $player->getInventory()->getItemInHand()->getDamage(),
            $player->getInventory()->getItemInHand()->getCount(),
            round($player->getX()),
            round($player->getY()),
            round($player->getZ()),
            $this->getPlayerFaction($player),
            $this->getFactionPower($player),
            $this->plugin->getServer()->getTickUsage(),
            $this->plugin->getServer()->getTicksPerSecond(),
            $player->getLevel()->getName(),
            $player->getLevel()->getFolderName(),
            $player->getAddress(),
            $player->getPing(),
            $this->plugin->getIslandOwner($player)
        ], $message);
        return (string)$message;
    }

    /**
     * @param Player $player
     * @return float|bool
     */
    public function getPlayerMoney(Player $player)
    {
        $economyAPI = $this->plugin->economy;
        if($economyAPI !== null){
            return $economyAPI->myMoney($player);
        } else {
            return 0;
        }
    }
    /**
     * @param $result
     * @param int $precision
     * @return string
     */
    public function formatInt($result, $precision = 1): string
    {
        $integer = is_numeric($result) ? $result : 0;
        $n = (int)$integer;
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = 'C';
        } else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }
        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }

        return $n_format . $suffix;
    }



    /**
     * @param Player $player
     * @return string
     */
    public function getPlayerRank(Player $player): string
    {
        /** @var PurePerms $purePerms */
        $purePerms = $this->plugin->rankPlugin;
        if ($purePerms !== null) {
            $group = $purePerms->getUserDataMgr()->getData($player)['group'];
            if ($group !== null) {
                return $group;
            } else {
                return "No Rank";
            }
        } else {
            return "Plugin not found";
        }
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getGem(Player $player): string
     {
     $this->Gem = $this->plugin->getServer()->getPluginManager ()->getPlugin("GemUI");
     if ($this->Gem){
        return $cs = $this->Gem->getGem($player->getName());
    }else{
        return $cs = "No Plugin";
    }
}
    /**
    * @param Player $player
    * @return float|string
    */
public function getPlayerCoin(Player $player){
  /** @var->getServer()-ntapi */
   $pointapi = $this->plugin->getServer()->getPluginManager()->getPlugin("CoinAPI");
        if($pointapi instanceof CoinAPI){
        return $pointapi->myCoin($player);
                 }else{
return "Plugin not found";
                 }
        }
public function getPlayerCS(Player $player){
 $this->CS = $this->plugin->getServer()->getPluginManager()->getPlugin("RebirthUI");
        if ($this->CS){
    return $cs1 = $this->CS->myReincarnated($player->getName());
          }else{
    return $cs1 = '§cNo Plugin';
            }
          }
        
    /**
     * @param Player $player
     * @return string
     */
    public function getPlayerFaction(Player $player): string
    {
        $factionsPro = $this->plugin->factionPro;
        if ($factionsPro !== null) {
            $factionName = $factionsPro->getPlayerFaction($player->getName());
            if ($factionName === null) {
                return "No Faction";
            }
            return $factionName;
        }
        return "Plugin not found";
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getFactionPower(Player $player): string
    {
        $factionsPro = $this->plugin->factionPro;
        if ($factionsPro !== null) {
            $factionName = $factionsPro->getPlayerFaction($player->getName());
            if ($factionName === null) {
                return "No Faction";
            }
            return $factionsPro->getFactionPower($factionName);
        }
        return "Plugin not found";
    }

}