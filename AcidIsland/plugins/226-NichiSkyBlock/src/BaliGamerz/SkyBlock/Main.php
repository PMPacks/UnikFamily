<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock;

use BaliGamerz\SkyBlock\command\SkyBlockCommand;
use BaliGamerz\SkyBlock\Entity\NpcClass;
use BaliGamerz\SkyBlock\Entity\SkyBlockTNT;
use BaliGamerz\SkyBlock\FunctionLogic\ListFunction;
use BaliGamerz\SkyBlock\libraries\VoidGenerator;
use BaliGamerz\SkyBlock\libraries\xenialdan\apibossbar\DiverseBossBar;
use BaliGamerz\SkyBlock\Menu\PublicMenu;
use BaliGamerz\SkyBlock\Menu\ShopLogic;
use BaliGamerz\SkyBlock\score\ScoreBuilder;
use BaliGamerz\SkyBlock\SkyBlockManager\SkyBlockManager;
use onebone\economyapi\EconomyAPI;
use onebone\coinapi\CoinAPI;
use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use BaliGamerz\SkyBlock\invitation\InvitationHandler;
use BaliGamerz\SkyBlock\island\IslandManager;


class Main extends PluginBase{

    /** @var Main */
    private $plugin;


    /** @var Main */
    private static $object = null;

    /** @var SkyBlockManager */
    private $SkyBlockManager;

    /** @var IslandManager */
    private $islandManager;

    /** @var InvitationHandler */
    private $invitationHandler;


    /** @var EventListener */
    private $eventListener;

    /**
     * @var array $islandTop
     */
    public $islandTop = [];

    /**
     * @var array $playerDataPath
     */
    public $playerDataPath = [];
    /**
     * @var array $config
     */
    public $config;
    /**
     * @var EconomyAPI
     */
    public $economy = null;
    /**
     * @var array $dataThemas
     */
    public $dataThemas;

    public $scoreTime = 3;

    /**
     * @var array $queue
     */
    public $queue = [];
    /**
     * @var ScoreBuilder
     */
    private $score;
    /** @var array $shops */
    public $shops;

    /**
     * @var array $lobbyData
     */
    public $lobbyData;
    /**
     * @var DiverseBossBar
     */
    public $bossBar;
    /**
     * @var Position
     */
    public $lobbyServer = null;

    /**
     * @var array $quests
     */
    public $quests;

    public $rankPlugin = null;
    public $factionPro = null;

    /**
     * @var array $disablePlayer
     */
    public $disablePlayer = [];

    public function onLoad()
    {
        $this->initialize();
    }

    public function onEnable()
    {
        $this->score = new ScoreBuilder($this);
        if ($this->hasBossBar()) {
            $this->bossBar = new DiverseBossBar();
        }
        Entity::registerEntity(NpcClass::class, true);
        Entity::registerEntity(SkyBlockTNT::class, true);

        GeneratorManager::addGenerator(VoidGenerator::class, "void", true);

        $this->setIslandManager();
        $this->setEventListener();
        $this->setSkyBlockManager();
        $this->setInvitationHandler();
        $this->setPluginHearbeat();
        $this->registerCommand();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->setLobby();
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (!self::$object instanceof Main) {
            self::$object = $this;
        }
        if (count($this->dataThemas['list']) > 0) {
            foreach ($this->dataThemas['list'] as $name => $data) {
                Utils::extractZipFile($data['dirname']);
            }
        }
        if ($this->hasBossBar()) {
            $this->getBossBar()->setTitle("§b» §l§eUnikFamily§r §b«");
            $this->getBossBar()->setPercentage(100);
        }
        $this->rankPlugin = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->factionPro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");

    }


    public function setLobby()
    {
        if ($this->lobbyData["lobbyPosition"]["vector"] !== false) {
            if ($this->getServer()->isLevelLoaded($this->lobbyData['lobbyPosition']["level"])) {
                $ex = explode(":", $this->lobbyData["lobbyPosition"]["vector"]);
                $this->lobbyServer = new Position((int)$ex[0], (int)$ex[1], (int)$ex[2], $this->getServer()->getLevelByName($this->lobbyData['lobbyPosition']["level"]));
            } else {
                $this->getLogger()->warning("Lobby server not loaded. Server has been shutdown");
                $this->getServer()->shutdown();
            }
        }
    }

    /**
     * @param Player $player
     * @param null $data
     */
    public function executeAddQuestsSystem(Player $player, $data = null)
    {
        $name = strtolower($player->getName());
        if ($data !== null) {
            $this->playerDataPath[$name]['objectives']['objectiveData'] = $data;
            $player->sendMessage("  §6§lNEW OBJECTIVE\n  §r§f" . $this->getPlayerObjective($player));
            Utils::addSound($player, 500, 0.3, 'note.harp');
            if ($this->hasBossBar()) {
                $this->bossBar->setTitleFor([$player], "§fObjective: §e" . $this->getPlayerObjective($player));
            }
            return;
        }
        Utils::addSound($player, 5, 1, 'random.levelup');
        $player->sendMessage("  §6§lThông Báo:\n  §r§f Hoàn Thành Nhiệm Vụ");
        $this->playerDataPath[$name]['objectives']['objectiveData'] = [];
        if ($this->hasBossBar()) {
            $this->bossBar->setTitleFor([$player], "§b» §l§eUnikFamily§r §b«");
        }
    }


    /**
     * @param Player $player
     */

    public function addPlayerQuest(Player $player)
    {
        $playerConfig = $this->getPlayerConfig($player);
        $running = $playerConfig['objectives']['running'];
        if ($this->hasPlayerQuest($player) === null) {
            if (isset($this->quests['quests'][$running])) {
                $this->executeAddQuestsSystem($player, $this->quests['quests'][$running]);
            } else {
                $this->executeAddQuestsSystem($player);
            }
        }
    }


    /**
     * @param Player $sender
     * @param $block
     * @param $type
     */
    public function runningQuest(Player $sender, $block, $type)
    {
        $data = $this->hasPlayerQuest($sender);
        $name = strtolower($sender->getName());
        $dataNext = null;
        if ($data !== null) {
            if ($data['objectiveData']['type'] === $type) {
                if ($block->getId() === $data['objectiveData']['item']['id'] and $block->getDamage() === $data['objectiveData']['item']['meta']) {
                    $this->playerDataPath[$name]['objectives']['objectiveData']['progress'] += 1;
                    $dataUpdate = $this->playerDataPath[$name]['objectives']['objectiveData'];
                    if ($dataUpdate['progress'] >= $dataUpdate['item']['amount']) {
                        $this->playerDataPath[$name]['objectives']['running']++;
                        $dataNext = $this->playerDataPath[$name];
                        $this->playerDataPath[$name]['objectives']['success'][] = $data['objectiveData']['questID'];
                        $this->playerDataPath[$name]['objectives']['objectiveData'] = [];
                        $this->addPlayerReward($sender, $dataUpdate['rewardCommands'] ?? [], $dataNext);
                        $this->addPlayerQuest($sender);
                    }
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param array $cmd
     * @param array $exp
     */
    public function addPlayerReward(Player $player, array $cmd, array $data)
    {
        foreach ($cmd as $command) {
            $this->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(), str_replace([
                "{player}",
                "{name}"
            ], [
                $player,
                $player->getDisplayName(),
            ], $command));
        }
        $island = $this->getIslandManager()->getIslandByPlayer($player);
        if ($island !== null) {
            $dataNext = $data['objectives'];
            $total = (count(['success']) + ($dataNext['running'] * 5) + ($dataNext['objectiveData']['item']['amount'] / 2) + 15 * 1.5 * 2);
            $rewardSummary = "§3--------------------------------\n\n§bTổng kinh nghiệm kiếm được: ".$total."\n\n§3--------------------------------";
            $island->addProgress($total);
            $player->sendMessage($rewardSummary);
        }
    }

    public function onDisable()
    {
        file_put_contents($this->getDataFolder() . "users.yml", yaml_emit($this->playerDataPath));
        file_put_contents($this->getDataFolder() . "islandtop.yml", yaml_emit($this->islandTop));
        file_put_contents($this->getDataFolder() . "themes", yaml_emit($this->dataThemas));
        file_put_contents($this->getDataFolder() . "lobby.yml", yaml_emit($this->lobbyData));
        file_put_contents($this->getDataFolder() . "Quests.yml", yaml_emit($this->quests));
        $this->islandManager->updateDisableIslandServer();
    }

public function addScore(Player $player, string $title, array $lines)
    {
        if (isset($this->disablePlayer[strtolower($player->getName())])) return;
        if ($this->scoreTime <= 0) {
            $this->score->build($player, $title, $lines);
            $this->scoreTime = $this->config['update-score'];
        }
        $this->scoreTime--;
        if (!$this->config['hud']['enable']) return;
        $player->sendPopup(str_replace(["{name}", "{rank}", "{money}", "{point}", "{gem}", "{health}", "{max_health}", "{defense}", "{mana}", "&"], [$player->getName(), $this->getPlayerRank($player),$this->getPlayerMoney($player), $this->getPlayerCoin($player), $this->getPlayerGem($player),round($player->getHealth(), 2), $player->getMaxHealth(), round($this->getArmorPoints($player)), $this->getPlayerMana($player), "§"], $this->config['hud']['format']));
    }
    public function getPlayerMoney(Player $player){
        /** @var EconomyAPI $economyAPI */
        $economyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if($economyAPI instanceof EconomyAPI){
            return $economyAPI->myMoney($player);
        }else{
            return "Plugin not found";
        }
    }

    public function getPlayerRank(Player $player): string{
        /** @var PurePerms $purePerms */
        $purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        if($purePerms instanceof PurePerms){
            $group = $purePerms->getUserDataMgr()->getData($player)['group'];
            if($group !== null){
                return $group;
            }else{
                return "No Rank";
            }
        }else{
            return "Plugin not found";
        }
    }
    public function getPlayerCoin(Player $player){
        /** @var PointAPI $pointapi */
        $pointapi = $this->getServer()->getPluginManager()->getPlugin("CoinAPI");
        if($pointapi instanceof CoinAPI){
            return $pointapi->myCoin($player);
        }else{
            return "Plugin not found";
        }
    }
    public function getPlayerGem(Player $player){
        $this->Gem = $this->getServer()->getPluginManager()->getPlugin("GemUI");
        if ($this->Gem){
                  return $cs = $this->Gem->getGem($player->getName());
          }else{
                     return $cs = '§cNo Plugin';
        }
    }
    /**
     * Returns how many armour points this mob has. Armour points provide a percentage reduction to damage.
     * For mobs which can wear armour, this should return the sum total of the armour points provided by their
     * equipment.
     * @param Player $player
     * @return int
     */
    public function getArmorPoints(Player $player): int
    {
        $total = 0;
        foreach ($player->getArmorInventory()->getContents() as $item) {
            $total += $item->getDefensePoints();
        }
        return $total;
    }

    /**
     * @param Player $player
     * @param string $type
     * @return string
     */
    public function getFormatIslandTop(Player $player, string $type): string
    {
        $index = $this->getIndexUserIslandTop($player, $type);
        $title = strtoupper($type);
        $page = $index + 1;
        $dataIsland = $this->islandTop['island'];
        $allPage = ceil(count($dataIsland) / 10);
        if (count($dataIsland) > 0) {
            $rows = [];
            foreach ($dataIsland as $name => $value) {
                $rows[$name] = $value[$type];
            }
            arsort($rows);
            $counter = 1;
            $rows = array_splice($rows, $index * 10, 10);
            $text = "§7§l[§eSkyBlock TOP ISLAND " . $title . "§7]\n§a=====§fPAGE §7(§a{$page}§7/§e{$allPage}§7) §a=====";
            foreach ($rows as $name => $value) {
                $rank = $index * 10 + $counter;
                $text .= "\n§d[§f" . $rank . "§d] §f" . $name . "§a » §e" . number_format($value) . "\n\n\n\n";
                $counter++;
            }
        } else {
            $text = "§7§l[§eSkyBlock TOP ISLAND " . $title . "§7]\n§a=======§f+§a======\n";
            $text .= "Island - NOT FOUND ISLAND\n";
        }
        $text .= "\n§a============§f^§a============\n   \n§eNhấp vào 1 ô bất kì để di chuyển\n§aNhấn để di chuyển";
        return $text;
    }


    /**
     * @param Player $player
     * @param $type
     * @return int
     */
    public function getIndexUserIslandTop(Player $player, $type): int
    {
        return $this->playerDataPath[strtolower($player->getName())]['queueIndexTop'][$type];
    }

    /**
     * @param Player $player
     * @param $type
     * @return bool
     */
    public function reduceIndexUserIslandTop(Player $player, $type): bool
    {
        if ($this->getIndexUserIslandTop($player, $type) < 1) {
            return false;
        }
        $this->playerDataPath[strtolower($player->getName())]['queueIndexTop'][$type] -= 1;
        return true;
    }

    /**
     * @param Player $player
     * @param $type
     * @return bool
     */
    public function addIndexUserIslandTop(Player $player, $type): bool
    {
        if (($this->getIndexUserIslandTop($player, $type) + 1) >= ceil(count($this->islandTop['island']) / 10)) {
            return false;
        }
        $this->playerDataPath[strtolower($player->getName())]['queueIndexTop'][$type] += 1;
        return true;
    }

    /**
     * @return DiverseBossBar
     */
    public function getBossBar(): DiverseBossBar
    {
        return $this->bossBar;
    }

    /**
     * @return bool
     */
    public function hasBossBar(): bool
    {
        return $this->config['bossbar'];
    }

    /**
     * @return ScoreBuilder
     */
    public function getScore(): ScoreBuilder
    {
        return $this->score;
    }

    public static function sendMessage($sender, $message)
    {
        $sender->sendMessage(str_replace("&", "§", "§f[§aSKYBLOCK§f] §f» §b" . $message));
    }

    /**
     * @param $sender
     * @param $message
     */
    public function sendMessageNonStatic($sender, $message)
    {
        $sender->sendMessage(str_replace("&", "§", "§f[§aSKYBLOCK§f] §f» §b" . $message));
    }


    /**
     * @param Player $player
     * @return int
     */
    public function getPlayerMana(Player $player): int
    {
        return $this->playerDataPath[strtolower($player->getName())]['mana'];
    }

    /**
     * @param Player $player
     * @param $amount
     * @return bool
     */
    public function reducePlayerMana(Player $player, $amount): bool
    {
        $name = strtolower($player->getName());
        $mana = $this->playerDataPath[$name]['mana'];
        if ($mana - $amount < 0) {
            return false;
        }
        $this->playerDataPath[$name]['mana'] -= $amount;
        return true;

    }

    /**
     * @param Player $player
     * @param $amount
     * @return bool
     */
    public function addPlayerMana(Player $player, $amount): bool
    {
        $name = strtolower($player->getName());
        $mana = $this->playerDataPath[$name]['mana'];
        if ($mana + $amount > $this->config['user']['max-mana']) {
            return false;
        }
        $this->playerDataPath[$name]['mana'] += $amount;
        return true;
    }

    /**
     * @param Player $player
     * @param $amount
     * @return bool
     */
    public function setPlayerMana(Player $player, $amount): bool
    {
        $name = strtolower($player->getName());
        if ($amount > $this->config['user']['max-mana']) {
            return false;
        }
        $this->playerDataPath[$name]['mana'] = $amount;
        return true;
    }


    /**
     * Return Main instance
     *
     * @return Main
     */
    public static function getInstance(): Main
    {
        return self::$object;
    }

    /**
     * Return SkyBlockBlockManager instance
     *
     * @return SkyBlockManager
     */
    public function getSkyBlockManager(): SkyBlockManager
    {
        return $this->SkyBlockManager;
    }

    /**
     * Return island manager
     *
     * @return IslandManager
     */
    public function getIslandManager(): IslandManager
    {
        return $this->islandManager;
    }

    /**
     * Return EventListener instance
     *
     * @return EventListener
     */
    public function getEventListener(): EventListener
    {
        return $this->eventListener;
    }

    /**
     * Return InvitationHandler instance
     *
     * @return InvitationHandler
     */
    public function getInvitationHandler(): InvitationHandler
    {
        return $this->invitationHandler;
    }

    /**
     * Register SkyBlockBlockManager instance
     */
    public function setSkyBlockManager()
    {
        $this->SkyBlockManager = new SkyBlockManager($this);
    }

    /**
     * Register IslandManager instance
     */
    public function setIslandManager()
    {
        $this->islandManager = new IslandManager($this);
    }

    /**
     * Register EventListener instance
     */
    public function setEventListener()
    {
        $this->eventListener = new EventListener($this);
    }

    /**
     * Schedule the PluginHearbeat
     */
    public function setPluginHearbeat()
    {
        $this->getScheduler()->scheduleRepeatingTask(new PluginHearbeat($this), 20);
    }

    /**
     * Register InvitationHandler instance
     */
    public function setInvitationHandler()
    {
        $this->invitationHandler = new InvitationHandler($this);
    }


    /**
     * @param Player $player
     * @return array|null
     */
    public function hasPlayerQuest(Player $player): ?array
    {
        $data = $this->getPlayerConfig($player)['objectives'];
        if (count($data['objectiveData']) > 0) {
            return $data;
        }
        return null;
    }


    /**
     * @param Player $player
     * @return int
     */
    public function getPlayerCompletedQuest(Player $player): int
    {
        return count($this->getPlayerConfig($player)['objectives']['success']);
    }

    /**
     * @param $sender
     * @return int
     */
    public function getIntProgress($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return $this->formatInt($island->level['progress']);
    }

    /**
     * @param $sender
     * @return int|string
     */
    public function getProgressSize($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return $this->formatInt($island->level['progressSize']);
    }

    /**
     * @param $sender
     * @return int|string
     */
    public function getIslandMoney($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return 0;
        }
        return $island->myMoney();
    }

    /**
     * @param $sender
     * @return int|string
     */
    public function getIslandMaxMoney($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return 0;
        }
        return (int)$island->money['max-money'];
    }

    /**
     * @param $sender
     * @return int|string
     */
    public function getIslandMoneyTier($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return 0;
        }
        return (int)$island->money['tier'];
    }

    /**
     * @param $sender
     * @return array|string
     */
    public function getNextIslandMoneyTier($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            ["next-int" => 0, 'next-money' => 0, 'price' => 0];
        }
        return ["next-int" => $island->money['tier'] + 1, 'next-money' => ($island->money['max-money'] * (int)$island->money['tier']) + 1, 'price' => $island->money['max-money'] * (int)$island->money['tier']];
    }

    /**
     * @param $sender
     * @return int
     */
    public function getLevel($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return $island->getLevel();
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getMine(Player $player)
    {
        $island = $this->islandManager->getIslandByPlayer($player);
        if ($island === null) {
            return "-";
        }
        return $island->getMine();
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getProgress(Player $player): string
    {
        $island = $this->islandManager->getIslandByPlayer($player);
        if ($island === null) {
            return "-";
        }
        return Utils::getProgress($island->getProgress());
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getIslandOwner(Player $player): string
    {
        $island = $this->islandManager->getIslandByPlayer($player);
        if ($island === null) {
            return "-";
        }
        return $island->getOwnerName();
    }

    /**
     * @param $sender
     * @return string
     */
    public function getIslandName($sender): string
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return $island->getIdentifier();
    }

    /**
     * @param $sender
     * @return int|string
     */
    public function getMemberOnline($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return count($island->getPlayersOnline());
    }

    /**
     * @param $sender
     * @return int|string
     */
    public function getAllMember($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return count($island->getAllMembers());

    }

    /**
     * @param Player $player
     * @return string
     */
    public function getPlayerObjective(Player $player): string
    {
        $dataObjective = $this->getPlayerConfig($player)['objectives'];
        if (isset($dataObjective['objectiveData']['name'])) {
            return $dataObjective['objectiveData']['name'];
        }
        return "-";
    }


    /**
     * @param Player $player
     * @return string
     */
    public function getPlayerObjectiveByIndex(Player $player): string
    {
        $dataObjective = $this->getPlayerConfig($player)['objectives'];
        if (isset($dataObjective['objectiveData']['running'])) {
            return $dataObjective['objectiveData']['running'];
        }
        return "-";
    }

    /**
     * @param Player $player
     * @return string
     */
    public function getPlayerObjectiveByProgress(Player $player): string
    {
        $dataObjective = $this->getPlayerConfig($player)['objectives'];
        if (isset($dataObjective['objectiveData']['name'])) {
            return "§7[§a{$dataObjective['objectiveData']['progress']}§7/§b{$dataObjective['objectiveData']['item']['amount']}§7]";
        }
        return "-";
    }


    /**
     * @param $sender
     * @return int|string
     */
    public function getIslandSize($sender)
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        return $island->getSize();
    }

    /**
     * @param $sender
     * @return string
     */
    public function getTranslateSize($sender): string
    {
        $island = $this->getIslandManager()->getIslandByPlayer($sender);
        if ($island === null) {
            return "-";
        }
        $c = $island->getSize();
        if ($c < 1) {
            return "EXTRA_SMALL";
        }
        if ($c >= $this->config["CategoryByBlocks"]["XS"]) {
            return "EXTRA_SMALL";
        }
        if ($c >= $this->config["CategoryByBlocks"]["S"]) {
            return "SMALL";
        }
        if ($c >= $this->config["CategoryByBlocks"]["M"]) {
            return "MEDIUM";
        }
        if ($c >= $this->config["CategoryByBlocks"]["L"]) {
            return "LARGE";
        }
        if ($c >= $this->config["CategoryByBlocks"]["XL"]) {
            return "EXTRA_LARGE";
        }
        if ($c > -1 and $c > 0 and $c < $this->config["CategoryByBlocks"]["S"]) {
            return "EXTRA SMALL";
        }
        return "Unknown";
    }

    /**
     * Register SkyBlockBlock command
     */
    public
    function registerCommand()
    {
        $this->getServer()->getCommandMap()->register("UnikSkyBlock", new SkyBlockCommand("skyblock", $this));
    }

    /**
     * Init Files
     */
    public
    function initialize()
    {
        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        if (!is_dir($this->getDataFolder() . "islands")) {
            @mkdir($this->getDataFolder() . "islands");
        }
        foreach ($this->getResources() as $file) {
            $this->saveResource($file->getFilename());
        }
        $this->saveResource("Skin/king_slime.png");
        $this->saveResource("Skin/king_slime.json");
        $this->saveResource("Skin/leaderboard.json");
        $this->saveResource("Skin/leaderboard.png");

        $this->islandTop = yaml_parse(file_get_contents($this->getDataFolder() . "islandtop.yml"));
        $this->playerDataPath = yaml_parse(file_get_contents($this->getDataFolder() . "users.yml"));
        $this->config = yaml_parse(file_get_contents($this->getDataFolder() . "config.yml"));
        $this->dataThemas = yaml_parse(file_get_contents($this->getDataFolder() . "themes.yml"));
        $this->lobbyData = yaml_parse(file_get_contents($this->getDataFolder() . "lobby.yml"));
        $this->shops = yaml_parse(file_get_contents($this->getDataFolder() . "shops.yml"));
        $this->quests = yaml_parse(file_get_contents($this->getDataFolder() . "Quests.yml"));
    }

    /**
     * Score data reloading
     */
    public
    function scoreReload()
    {
        $this->config = yaml_parse(file_get_contents($this->getDataFolder() . "config.yml"));
    }

    /**
     * Return player config
     *
     * @param Player $player
     * @return array
     */
    public function getPlayerConfig(Player $player): ?array
    {
        if (isset($this->playerDataPath[strtolower($player->getName())])) {
            return $this->playerDataPath[strtolower($player->getName())];
        }
        return null;
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "join":
                if ($sender instanceof Player) {
                    (new ListFunction())->joinFunction($sender);
                } else {
                    $sender->sendMessage("§cPlease run this command in-game.");
                }
                break;
            case "score":
                if ($sender instanceof Player) {
                    $name = strtolower($sender->getName());
                    if (isset($this->disablePlayer[$name])) {
                        unset($this->disablePlayer[$name]);
                        $sender->sendMessage("§aSuccessfully enabled ScoreHud.");
                    } else {
                        $this->disablePlayer[$name] = 1;
                        $this->getScore()->unBuild($sender);
                        $sender->sendMessage("§cSuccessfully disabled ScoreHud.");
                    }
                } else {
                    $sender->sendMessage("§cPlease run this command in-game.");
                }
                break;
            case "mana":
                if ($sender instanceof Player) {
                    (new PublicMenu())->buyMana($sender);
                } else {
                    $sender->sendMessage("§cPlease run this command in-game.");
                }
                break;
            case "visit":
                if ($sender instanceof Player) {
                    if (count($args) < 1) {
                        (new PublicMenu())->visitIsland($sender);
                    } else {
                        $player = $this->getServer()->getPlayer($args[0]);
                        if ($player !== null) {
                            $island = $this->getIslandManager()->getIslandByPlayer($player);
                            if ($island !== null) {
                                if ($island->isLocked()) {
                                    $this->sendMessageNonStatic($sender, "Hòn đảo này bị khóa, bạn không thể xem");
                                } else {
                                    $sender->teleport(new Position($island->getPosition()->x, $island->getPosition()->y + 2, $island->getPosition()->z, $this->getServer()->getLevelByName($island->getIdentifier())));
                                    $this->sendMessageNonStatic($sender, "Bạn đã tham gia để xem đảo thành công");
                                }
                            } else {
                                $this->sendMessageNonStatic($sender, "Ít nhất thành viên trên đảo SkyBlock phải hoạt động nếu bạn muốn nhìn thấy hòn đảo!");
                            }
                        } else {
                            $sender->sendMessage("§cKhông tìm thấy Player!. Sử dụng: /visit [playername] | /visit");
                        }
                    }
                } else {
                    $sender->sendMessage("§cPlease run this command in-game.");
                }
                break;
            case "buyisland":
                if ($sender instanceof Player) {
                    (new PublicMenu())->buyIsland($sender);
                } else {
                    $sender->sendMessage("§cPlease run this command in-game.");
                }
                break;
            case "buy":
                if (!$sender instanceof Player) {
                    $sender->sendMessage("§cPlease run this command in-game.");
                    return false;
                }
                if (!isset($args[0])) {
                    $sender->sendMessage("Usage: /buy [Category]");
                    return false;
                }
                $shop = new ShopLogic();
                $shops = $this->shops['shop'];
                $type = strtolower($args[0]);
                if (!array_key_exists($type, $shops)) {
                    $sender->sendMessage("§eDanh sách các cửa hàng thể loại ");
                    foreach ($shops as $name => $value) {
                        $sender->sendMessage("§d- §f" . $name);
                    }
                    return false;
                }
                $shop->onOpen2($sender, $type, $shops[$type]['name']);
                break;
            case "addquest":
                if (!$sender instanceof Player) {
                    $sender->sendMessage("§cPlease run this command in-game.");
                    return false;
                }
                (new PublicMenu())->addQuests($sender, "");
                break;
        }
        return true;
    }

public function formatInt($number, $per = 0): string
    {
        $result = is_numeric($number) ? $number : 0;
        $integer = (int)$result;

        $key = [
            12 => 'T',
            9 => 'B',
            7 => 'M',
            5 => 'K',
            0 => ''
        ];
        foreach ($key as $exponent => $abbrev) {
            if (abs($integer) >= pow(10, $exponent)) {
                $display = $integer / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals) . $abbrev;
                break;
            }
        }
        return $number;
    }
} 