<?php

namespace instantlyta\fcaclan;

use _64FF00\PureChat\PureChat;
use instantlyta\fcaclan\event\ClanChangeEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
//use LDX\iProtector\Main as Iprotect;

class FCAClan extends PluginBase implements Listener
{
    const OWNER_RANK = 3;

    const ACTION_INVITE = 0;
    const ACTION_KICK = 1;
    const ACTION_REQUEST_CONTROL = 2;
    const ACTION_SET_MOTD = 3;
    const ACTION_SET_RANK = 4;
    const ACTION_LEVEL_UP = 5;

    const SETTING_MAX_PLAYERS = 0;
    const SETTING_REQUIRED_POINT = 1;
    const SETTING_REQUIRED_MONEY = 2;
    const SETTING_CLAN_TAG = 3;

    private $settings = [];

    private $clans = [];
    private $invitePending = [];
    private $inviteSendConfirm = [];
    private $kickPending = [];
    private $quitPending = [];
    private $welcomePending = [];
    private $clanPromotePending = [];
    private $clanDeletePending = [];

    public $topClan = [];

    private static $instance = null;

    public function onLoad()
    {
        self::$instance = $this;
    }

    /**
     * @return FCAClan
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->reloadConfig();
        @mkdir($this->getDataFolder() . "profiles/");
        $this->clans = (new Config($this->getDataFolder() . "clans.yml", Config::YAML))->getAll();
        $this->chat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        $this->settings = $this->getConfig()->get("settings");
		$this->iprotect = $this->getServer()->getPluginManager()->getPlugin("iProtector");
        $this->updateTopClan();
        $this->getScheduler()->scheduleDelayedRepeatingTask(new UpdateTopClanTask(), 36000, 36000);
    }

    public function onDisable()
    {
        $this->save();
    }

    // Events

    /*public function onJoin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        if ($this->haveClan($player)) {
            $player->setDisplayName("[§l§3• §a" . $this->getClanName($player) . "§f •]" . $player->getName());
        }
    }*/

    public function updateNametag(ClanChangeEvent $event)
    { // PureChat API
        /** @var PureChat $pureChat */
        $pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        if ($pureChat === null) return;
        $p = $event->getPlayer();

        $isMultiWorldSupportEnabled = $pureChat->getConfig()->get("enable-multiworld-support");

        $levelName = $isMultiWorldSupportEnabled ? $p->getLevel()->getName() : null;

        $p->setNameTag($pureChat->getNameTag($p, $levelName));
    }


    public function onDamagee(EntityDamageEvent $event)
    {
        if($event->isCancelled() or !$this->iprotect->canGetHurt($event->getEntity())){
	    return false;
		}else{
            if ($event instanceof EntityDamageByEntityEvent) {
                $damager = $event->getDamager();
                $entity = $event->getEntity();
                if (!($damager instanceof Player) || !($entity instanceof Player) || !$this->iprotect->canGetHurt($damager)) return;
                if ($this->haveClan($damager) && $this->haveClan($entity) && $this->getProfile($damager)->get("clan") === $this->getProfile($entity)->get("clan")) {
                    $event->setCancelled();
                    $damager->sendMessage("§l§3• §aNgười chơi §c" . $entity->getName() . "§a cùng Clan của bạn.");
                    return;
                } elseif ($entity->getHealth() - $event->getFinalDamage() <= 0) {
                    if ($this->haveClan($entity)) {
                        $this->clanAnnounce("§c" . $entity->getName() . " §abị người chơi§c " . $damager->getName() . ($this->haveClan($damager) ? " §e(Clan " . $this->getClanName($damager) . ")§a " : "") .
                            " §ađánh chết. Hãy trả thù!", $this->getClanName($entity));
                    }
                    if ($this->haveClan($damager)) {
                        $point = $this->haveClan($entity) ? 2 : 1;
                        $this->clanAnnounce("§c" . $damager->getName() . "§a đã giết §c" . $entity->getName() . "§a, giành §c$point §ađiểm về cho Clan!", $this->getClanName($damager));
                        $this->addPoint($point, $this->getClanName($damager));
                    }
                    return;
				}
			}
		}
	}

    protected function paginateText(CommandSender $sender, $pageNumber, array $txt)
    {
        $hdr = array_shift($txt);
        if ($sender instanceof ConsoleCommandSender) {
            $sender->sendMessage(TextFormat::GREEN . $hdr . TextFormat::RESET);
            foreach ($txt as $ln) $sender->sendMessage($ln);
            return true;
        }
        $pageHeight = 8;
        $lineCount = count($txt);
        $pageCount = intval($lineCount / $pageHeight) + ($lineCount % $pageHeight ? 1 : 0);
        $hdr = TextFormat::GREEN . $hdr . TextFormat::RESET;
        if ($pageNumber > $pageCount) {
            $sender->sendMessage($hdr);
            $sender->sendMessage("§3• §aKhông có trang Help này");
            return true;
        }
        $hdr .= TextFormat::RED . " ($pageNumber of $pageCount)";
        $sender->sendMessage($hdr);
        for ($ln = ($pageNumber - 1) * $pageHeight; $ln < $lineCount && $pageHeight--; ++$ln) {
            $sender->sendMessage($txt[$ln]);
        }
        return true;
    }

    protected function getPageNumber(array &$args)
    {
        $pageNumber = 1;
        if (count($args) && is_numeric($args[count($args) - 1])) {
            $pageNumber = (int)array_pop($args);
            if ($pageNumber <= 0) $pageNumber = 1;
        }
        return $pageNumber;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "clan":
                if (!isset($args[0])) {
                    $sender->sendMessage("§l§3• §cUsage: /clan help");
                    return true;
                }
                /** @var string[] $altArgs */
                $altArgs = array_slice($args, 1);
                switch ($args[0]) {
                    case "help":
                        $help = [
                            "§l§c-+==§b Showing Clan Help §c=+- ",
                            "§cClan §f↣ §e/c : §achat kênh Clan",
                            "§cClan §f↣ §e/clan top : §axem xếp hạng Clan trong server",
                            "§cClan §f↣ §e/clan create : §atạo Clan mới",
                            "§cClan §f↣ §e/clan delete : §axoá Clan của bạn",
                            "§cClan §f↣ §e/clan join : §agửi đơn xin gia nhập Clan",
                            "§cClan §f↣ §e/clan quit : §athoát Clan của bạn",
                            "§cClan §f↣ §e/clan accept : §achấp nhận lời mời vào Clan",
                            "§cClan §f↣ §e/clan decline : §atừ chối lời mời vào Clan",
                            "§cClan §f↣ §e/clan donate : §acống hiến cho Clan",
                            "§cClan §f↣ §e/clan lookup : §axem người chơi thuộc Clan nào",
                            "§cClan §f↣ §e/clan requestlist : §axem danh sách các thành viên xin vào Clan",
                            "§cClan §f↣ §e/clan status : §axem thông tin Clan",
                            "§cClan §f↣ §e/clan invite : §amời thêm thành viên vào Clan",
                            "§cClan §f↣ §e/clan kick : §akick thành viên khỏi Clan",
                            "§cClan §f↣ §e/clan motd : §asửa Motd Clan",
                            "§cClan §f↣ §e/clan setrank : §aset chức vụ cho thành viên trong Clan",
                            "§cClan §f↣ §e/clan levelup : §anâng cấp Clan",
                        ];
                        $this->paginateText($sender, $this->getPageNumber($args), $help);
                        break;
                    case "create":
                        if ($sender instanceof ConsoleCommandSender || $sender instanceof RemoteConsoleCommandSender) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") !== false) {
                            $sender->sendMessage("§3• §aBạn đã có clan.");
                            return true;
                        }
                        if (count($altArgs) <> 1) {
                            $cost = $this->getConfig()->get("create-cost");
                            $sender->sendMessage("§3• §cUsage: /clan create <clanName>\nLưu ý: Tạo Clan tốn $cost xu!");
                            return true;
                        }

                        if (strpos($altArgs[0], "§")) {
                            $sender->sendMessage("§3•§c Không được ghi màu trong lên clan.");
                            return true;
                        }

                        if (strlen($altArgs[0]) > 20) {
                            $sender->sendMessage("§3•§c Tên clan phải ngắn hơn 20 kí tự.");
                            return true;
                        }

                        if (isset($this->clans[$altArgs[0]])) {
                            $sender->sendMessage("§3•§c Clan đã tồn tại.");
                            return true;
                        }

                        $eco = EconomyAPI::getInstance();
                        $money = $eco->myMoney($sender);
                        $cost = $this->getConfig()->get("create-cost");
                        if ($money < $cost) {
                            $sender->sendMessage("§3• §eBạn không có đủ tiền ($cost, bạn có $money)");
                            return true;
                        }

                        $eco->reduceMoney($sender, $cost);

                        $this->clans[$altArgs[0]] = [
                            "motd" => $altArgs[0],
                            "level" => 1,
                            "point" => 0,
                            "members" => [
                                strtolower($sender->getName()) => self::OWNER_RANK // see config.yml
                            ],
                            "request" => []
                        ];

                        $profile->set("clan", $altArgs[0]);
                        $profile->set("rank", self::OWNER_RANK);
                        $profile->save();

                        $this->save();
                        $sender->sendMessage("§3• §eTạo Clan thành công!");
                        break;
                    case "invite":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "accept":
                                if (isset($this->invitePending[$sender->getName()]) && $this->invitePending[$sender->getName()][0] > time()) {
                                    $inviter = $this->getServer()->getPlayerExact($this->invitePending[$sender->getName()][2]);
                                    if ($inviter !== null) {
                                        $inviter->sendMessage($sender->getName() . " §ađã chấp nhận lời mời vào clan của bạn.•");
                                    }

                                    $this->addPlayerToClan($sender, $this->invitePending[$sender->getName()][1]);

                                    unset($this->invitePending[$sender->getName()]);
                                    return true;
                                } else {
                                    $sender->sendMessage("§3• §aLời mời không tồn tại.");
                                    return true;
                                }
                                break;
                            case "decline":
                                if (!isset($this->invitePending[$sender->getName()])) {
                                    $sender->sendMessage("§3• §aBạn không được bất cứ ai mời.");
                                    return true;
                                } else {
                                    $inviter = $this->getServer()->getPlayerExact($this->invitePending[$sender->getName()][2]);
                                    if ($inviter !== null) {
                                        $inviter->sendMessage($sender->getName() . " §ađã từ chối lời mời vào clan của bạn.•");
                                    }
                                    unset($this->invitePending[$sender->getName()]);
                                    $sender->sendMessage("§3• §eĐã từ chối lời mời.");
                                    return true;
                                }
                                break;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_INVITE)) {
                            $sender->sendMessage("§3• §eBạn không có quyền mời người chơi vào clan.");
                            return true;
                        }
                        if ($this->isClanFull($profile->get("clan"))) {
                            $sender->sendMessage("§3• §eClan đã đầy.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->inviteSendConfirm[$sender->getName()]) && $this->inviteSendConfirm[$sender->getName()][0] > time()) {
                                    $player = $this->getServer()->getPlayerExact($this->inviteSendConfirm[$sender->getName()][1]);
                                    $this->invitePending[$player->getName()] = [time() + 50, $profile->get("clan"), $sender->getName()];
                                    $sender->sendMessage("§3• §aLời mời đã được gửi.");
                                    $player->sendMessage("§3•§d " . $sender->getName() . " §6đã gửi cho bạn một lời mời vào clan§b '" . $profile->get("clan") . "'.");
                                    $player->sendMessage("§3• §bBấm §c/clan invite accept§a để đồng ý hoặc decline để từ chối.");
                                    $player->sendMessage("§3• §cLời mời sẽ tự động hủy sau 50 giây.");
                                    return true;
                                } else {
                                    $sender->sendMessage("§3• §aLời mời không tồn tại.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->inviteSendConfirm[$sender->getName()])) {
                                    $sender->sendMessage("§3• §aBạn không mời bất cứ ai.");
                                    return true;
                                } else {
                                    unset($this->inviteSendConfirm[$sender->getName()]);
                                    $sender->sendMessage("§3• §aĐã hủy lời mời.");
                                    return true;
                                }
                                break;
                        }
                        if (count($altArgs) <> 1) {
                            $sender->sendMessage("§3• §cUsage: /clan invite <playerName>");
                            return true;
                        }
                        $player = $this->getServer()->getPlayer($altArgs[0]);
                        if ($player === null || $player->getName() === $sender->getName()) { // TODO this. is. bad.
                            $sender->sendMessage("§3• §eKhông tìm thấy người chơi nào với từ khóa '" . $altArgs[0] . "'");
                            return true;
                        } else if ($player->getName() === $altArgs[0] && $this->getProfile($player)->get("clan") !== false) {
                            $sender->sendMessage("§3• §eNgười chơi " . $altArgs[0] . " đã có clan rồi.");
                            return true;
                        }
                        if (strtolower($altArgs[0]) <> strtolower($player->getName())) {
                            $this->inviteSendConfirm[$sender->getName()] = [time() + 50, $player->getName()];
                            $sender->sendMessage("§3• §aChúng tôi không tìm thấy người chơi '" . $altArgs[0] . "'.\nCó phải bạn muốn mời '" . $player->getName() . "'? Gõ /clan invite <yes(có)|no(không)>");
                            $sender->sendMessage("§3• §aLời mời sẽ tự động hủy sau 50 giây.");
                            return true;
                        }
                        $this->invitePending[$player->getName()] = [time() + 50, $profile->get("clan"), $sender->getName()];
                        $sender->sendMessage("§3• §aLời mời đã được gửi.");
                        $player->sendMessage("§3•§d " . $sender->getName() . " §6đã gửi cho bạn một lời mời vào clan§b '" . $profile->get("clan") . "'.");
                        $player->sendMessage("§3• §bBấm §c/clan invite accept§a để đồng ý hoặc decline để từ chối.");
                        $player->sendMessage("§3• §cLời mời sẽ tự động hủy sau 50 giây.");
                        break;
                    case "my":
                    case "info":
                    case "status":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("§3• §aBạn không ở trong clan nào.");
                            return true;
                        }
                        $clan = $this->clans[$profile->get("clan")];
                        $mes = "§3• §eClan§a " . $clan["motd"] . " (" . $profile->get("clan") . "):\n";
                        $mes .= "§3• Cấp Clan: §a" . $clan["level"] . "\n";
                        $mes .= "§3• Điểm Clan: §a" . $clan["point"] . "\n";
                        $mes .= "§3• Chức vụ của bạn: §a" . $this->getRankName($profile->get("rank")) . "\n";
                        $mes .= "§3• Thành Viên:§c\n";
                        foreach ($clan["members"] as $name => $rank) {
                            $mes .= "  $name: " . $this->getRankName($rank) . "; ";
                        }
                        $sender->sendMessage($mes);
                        break;
                    case "kick":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_KICK)) {
                            $sender->sendMessage("§3• §eBạn không có quyền kick người chơi khỏi clan.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->kickPending[$sender->getName()]) && $this->kickPending[$sender->getName()][0] > time()) {
                                    $pName = $this->kickPending[$sender->getName()][2];
                                    $targetProfile = $this->getProfile($pName);
                                    if ($targetProfile->get("clan") !== $profile->get("clan")) {
                                        $sender->sendMessage("§3• §aNgười này không ở trong Clan của bạn.");
                                        unset($this->kickPending[$sender->getName()]);
                                        $sender->sendMessage("§3• §aĐã hủy yêu cầu kick.");
                                        return true;
                                    }
                                    $result = $this->removePlayerFromClan($pName);
                                    unset($this->kickPending[$sender->getName()]);
                                    $sender->sendMessage($result ? "§3• §aĐã kick người chơi." : "Có lỗi xảy ra trong quá trình kick.");
                                    return true;
                                } else {
                                    $sender->sendMessage("§3• §aYêu cầu không tồn tại.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->kickPending[$sender->getName()])) {
                                    $sender->sendMessage("§3• §aBạn không kick bất cứ ai.");
                                    return true;
                                } else {
                                    unset($this->kickPending[$sender->getName()]);
                                    $sender->sendMessage("§3• §aĐã hủy yêu cầu.");
                                    return true;
                                }
                                break;
                        }
                        if (count($altArgs) <> 1) {
                            $sender->sendMessage("§3• §cUsage: /clan kick <playerName>");
                            return true;
                        }
                        //$clan = $this->getClan($profile->get("clan"));
                        $needle = $altArgs[0];
                        $found = $this->getPlayerInClan($needle, $this->getClanName($sender), $sender->getName());
                        if ($found === null) {
                            $sender->sendMessage("§3• §eKhông tìm thấy người chơi nào với từ khóa '$needle' trong clan của bạn.");
                            return true;
                        }
                        $this->kickPending[$sender->getName()] = [time() + 50, $profile->get("clan"), $found];
                        $sender->sendMessage("§3• §cBạn có chắc chắn muốn kick '$found' khỏi clan?");
                        $sender->sendMessage("§3• §cBấm /clan kick yes để xác nhận.");
                        $sender->sendMessage("§3• §cYêu cầu kick sẽ tự động hủy sau 50 giây.");
                        break;
                    case "requestlist":
                    case "rl":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_REQUEST_CONTROL)) {
                            $sender->sendMessage("§l§3• §aBạn không có quyền chấp nhận/từ chối yêu cầu vào clan.");
                            return true;
                        }
                        $clan =& $this->clans[$profile->get("clan")];
                        if (isset($altArgs[0])) {
                            //in_array($altArgs[0], $clan["request"])
                            if (($found = $this->getPlayerInRequestList($altArgs[0], $profile->get("clan"))) !== null) {
                                if ($this->isClanFull($profile->get("clan"))) {
                                    $sender->sendMessage("§l§3• §aClan đã đầy.");
                                    return true;
                                }
                                $this->addPlayerToClan($found, $profile->get("clan"));
                                unset($clan["request"][array_search($found, $clan["request"])]);
                                $this->save();
                                $sender->sendMessage("§l§3• §aĐã chấp nhận yêu cầu này.");
                            } else {
                                $sender->sendMessage("§l§3• §aKhông tìm thấy người chơi này.");
                            }
                            return true;
                        }
                        $mes = "§l§3• §aDanh sách yêu cầu vào clan:\n";
                        foreach ($clan["request"] as $name) {
                            $mes .= "$name; ";
                        }
                        $mes .= "\nGõ /clan requestlist <playerName> để chấp nhận người chơi vào clan.";
                        $sender->sendMessage($mes);
                        break;
                    case "delete":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || $profile->get("rank") !== self::OWNER_RANK) {
                            $sender->sendMessage("§l§3• §aBạn không có quyền xóa clan.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->clanDeletePending[$sender->getName()]) && $this->clanDeletePending[$sender->getName()][0] > time()) {
                                    $this->clanAnnounce("§l§3• §aClan đã bị giải tán bởi Chủ Clan.", $profile->get("clan"));
                                    foreach ($this->clans[$profile->get("clan")]["members"] as $name => $rank) {
                                        $this->removePlayerFromClan($name);
                                    }
                                    unset($this->clans[$profile->get("clan")]);
                                    $this->save();
                                    unset($this->clanDeletePending[$sender->getName()]);
                                    $sender->sendMessage("§l§3• §aXóa clan thành công.");
                                    return true;
                                } else {
                                    $sender->sendMessage("§l§3• §aYêu cầu không tồn tại.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->clanDeletePending[$sender->getName()])) {
                                    $sender->sendMessage("§l§3• §aYêu cầu không tồn tại.");
                                    return true;
                                } else {
                                    unset($this->clanDeletePending[$sender->getName()]);
                                    $sender->sendMessage("§l§3• §aĐã hủy yêu cầu.");
                                    return true;
                                }
                                break;
                        }
                        $this->clanDeletePending[$sender->getName()] = [time() + 50];
                        $sender->sendMessage("§l§3• §aBạn đang xóa clan " . $profile->get("clan") . ".");
                        $sender->sendMessage("§l§3• §aNhập /clan delete yes để xác nhận");

                        break;
                    case "quit":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("§l§3• §aBạn không ở trong clan nào.");
                            return true;
                        }
                        if ($profile->get("rank") === self::OWNER_RANK) {
                            $sender->sendMessage("§l§3• §aBạn không thể thoát clan khi bạn là chủ clan. Hãy chuyển nhượng chức vụ trước.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->quitPending[$sender->getName()]) && $this->quitPending[$sender->getName()][0] > time()) {
                                    $result = $this->removePlayerFromClan($sender);
                                    unset($this->quitPending[$sender->getName()]);
                                    $sender->sendMessage($result ? "§l§3• §aThoát clan thành công." : "Có lỗi xảy ra trong quá trình thoát clan.");
                                    return true;
                                } else {
                                    $sender->sendMessage("§l§3• §aYêu cầu không tồn tại.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->quitPending[$sender->getName()])) {
                                    return true;
                                } else {
                                    unset($this->quitPending[$sender->getName()]);
                                    $sender->sendMessage("§l§3• §Đã hủy yêu cầu.");
                                    return true;
                                }
                                break;
                        }
                        $this->quitPending[$sender->getName()] = [time() + 50];
                        $sender->sendMessage("§l§3• §aBạn có chắc chắn muốn rời clan " . $profile->get("clan") . "?");
                        $sender->sendMessage("§l§3• §aNhập /clan quit yes để xác nhận");
                        break;
                    case "admin":
                        // TODO
                        break;
                    case "motd":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_SET_MOTD)) {
                            $sender->sendMessage("§l§3• §aBạn không có quyền chỉnh sửa MOTD của clan.");
                            return true;
                        }
                        if (!isset($altArgs[0])) {
                            $sender->sendMessage("§l§3• §cUsage: /clan motd <message>");
                            return true;
                        }
                        $this->clans[$profile->get("clan")]["motd"] = $altArgs[0];
                        $this->save();
                        $sender->sendMessage("§l§3• §aĐã chỉnh sửa MOTD.");
                        break;
                    case "welcome":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("§l§3• §aBạn không ở trong clan nào.");
                            return true;
                        }
                        if (isset($this->welcomePending[$profile->get("clan")])) {
                            foreach ($this->welcomePending[$profile->get("clan")] as $key => $data) {
                                if (!in_array($sender->getName(), $data[1])) {
                                    $this->clanChat($sender, "§l§3• §aChào mừng " . $data[0] . " tham gia clan nhé!");
                                    $this->welcomePending[$profile->get("clan")][$key][1][] = $sender->getName();
                                }
                            }
                        }
                        break;
                    case "setrank":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_SET_RANK)) {
                            $sender->sendMessage("§l§3• §aBạn không có quyền chỉnh sửa rank của thành viên clan.");
                            return true;
                        }
                        if (!isset($altArgs[0])) {
                            $sender->sendMessage("§l§3• §cUsage: /clan setrank <playerName> <rankLevel>"); // TODO Rank Name
                            $mes = "§l§3• §aDanh sách rank:§d \n";
                            foreach ($this->getConfig()->get("rank") as $rank => $info) {
                                $mes .= "$rank -> " . $info["name"] . "\n";
                            }
                            $sender->sendMessage($mes);
                            return true;
                        }
                        //$clan =& $this->clans[$profile->get("clan")];
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "yes":
                                if (isset($this->clanPromotePending[$sender->getName()]) && $this->clanPromotePending[$sender->getName()][0] > time()) {
                                    $newOwner = $this->clanPromotePending[$sender->getName()][1];
                                    $this->setRank($newOwner, self::OWNER_RANK);
                                    $this->setRank($sender, 1);
                                    unset($this->clanPromotePending[$sender->getName()]);
                                    $sender->sendMessage("§l§3• §aBạn đã chuyển nhượng Clan cho $newOwner");
                                    $this->clanAnnounce("§l§3• §aClan đã được chuyển nhượng toàn bộ quyền sở hữu cho $newOwner, hãy chúc mừng!", $profile->get("clan"));
                                    return true;
                                } else {
                                    $sender->sendMessage("§l§3• §aYêu cầu không tồn tại.");
                                    return true;
                                }
                                break;
                            case "no":
                                if (!isset($this->clanPromotePending[$sender->getName()])) {
                                    return true;
                                } else {
                                    unset($this->clanPromotePending[$sender->getName()]);
                                    $sender->sendMessage("§l§3• §aĐã hủy yêu cầu.");
                                    return true;
                                }
                                break;
                        }
                        if (isset($altArgs[0])) {
                            if (isset($altArgs[1])) {
                                $altArgs[1] = (int)$altArgs[1];
                                if (($found = $this->getPlayerInClan($altArgs[0], $profile->get("clan"), $sender->getName())) === null) {
                                    $sender->sendMessage("§l§3• §aNgười chơi không có trong clan.");
                                    return true;
                                }
                                $targetProfile = $this->getProfile($found);
                                if ($targetProfile->get("rank") >= $profile->get("rank")) {
                                    $sender->sendMessage("§l§3• §aNgười chơi có chức vụ cao hơn hoặc ngang bằng bạn.");
                                    return true;
                                }
                                if ((string)((int)$altArgs[1]) !== (string)$altArgs[1]) {
                                    $sender->sendMessage("§l§3• §aRank phải là một số.");
                                    return true;
                                }
                                $altArgs[1] = (int)$altArgs[1];
                                if ($altArgs[1] >= $profile->get("rank") && $profile->get("rank") !== self::OWNER_RANK) {
                                    $sender->sendMessage("§l§3• §aBạn không thể đặt chức vụ của người khác cao hơn hoặc ngang bằng bạn.");
                                    return true;
                                } else if ($altArgs[1] === $targetProfile->get("rank")) {
                                    $sender->sendMessage("§l§3• §aNgười này đã đang ở chức này rồi.");
                                    return true;
                                }
                                if ($altArgs[1] === self::OWNER_RANK) {
                                    if ($profile->get("rank") !== self::OWNER_RANK) {
                                        $sender->sendMessage("§l§3• §aBạn không có quyền chuyển nhượng quyền sở hữu clan.");
                                        return true;
                                    }
                                    $this->clanPromotePending[$sender->getName()] = [time() + 50, $found];
                                    $sender->sendMessage("§l§3• §aBạn có CHẮC CHẮN muốn chuyển quyền sở hữu Clan cho $found?");
                                    $sender->sendMessage("§l§3• §aBạn sẽ không còn quyền sở hữu Clan này và trở về rank " . $this->getRankName(1) . ".");
                                    $sender->sendMessage("§l§3• §aGõ /clan setrank yes để xác nhận. Yêu cầu chuyển nhượng sẽ bị hủy sau 50 giây.");
                                    return true;
                                }
                                if (!isset($this->getConfig()->get("rank")[$altArgs[1]])) {
                                    $sender->sendMessage("§l§3• §cKhông có rank này.");
                                    return true;
                                }
                                $promote = $altArgs[1] > $targetProfile->get("rank");
                                $this->setRank($found, $altArgs[1]);
                                $sender->sendMessage("§l§3• §aĐặt chức vụ thành công.");
                                $this->clanAnnounce($found . " đã được " . ($promote ? "nâng" : "hạ") . " chức " . $this->getRankName($altArgs[1]) . " bởi " . $sender->getName(), $profile->get("clan"));
                            }
                        }
                        break;
                    case "lookup":
                        if (count($altArgs) <> 1) {
                            $sender->sendMessage("§l§3• §cUsage: /clan lookup <playerName>");
                            return true;
                        }
                        $sender->sendMessage("§l§3• §aNgười chơi " . $altArgs[0] . " " . ($this->haveClan($altArgs[0]) ? "thuộc clan " . $this->getClanName($altArgs[0]) : "không thuộc clan nào."));
                        break;
                    case "reload":
                        if (!($sender->isOp())) return true;
                        $this->saveDefaultConfig();
                        $this->reloadConfig();
                        $sender->sendMessage("§l§3• §aĐã reload config.yml");
                        break;
                    case "top":
                        $mes = "§l§3• §aĐây là top 3 Clan xuất sắc nhất toàn Server:\n";
                        for ($i = 0; $i < 3; $i++) {
                            $mes .= "§e Top " . ($i + 1) . ":§6 " . (isset($this->topClan[$i]) ? $this->topClan[$i] : "§aChưa cập nhật") . "\n";
                        }
                        if ($sender instanceof Player && $this->haveClan($sender)) {
                            $mes .= "§l§3• §eClan bạn đang xếp thứ: §a" . (isset(array_flip($this->topClan)[$this->getClanName($sender)]) ? array_flip($this->topClan)[$this->getClanName($sender)] + 1 : "Chưa cập nhật") . "\n";
                        }
                        $mes .= "§l§3• §dTop Clan sẽ được cập nhật mỗi 30 phút.";
                        $sender->sendMessage($mes);
                        break;
                    case "levelup":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false || !$this->canDo($sender, self::ACTION_LEVEL_UP)) {
                            $sender->sendMessage("§l§3• §aBạn không có quyền nâng cấp clan.");
                            return true;
                        }
                        $clan = $this->getClan($profile->get("clan"));
                        $nextLevel = $clan["level"] + 1;
                        if ($nextLevel > $this->getConfig()->get("max-clan-level")) {
                            $sender->sendMessage("§l§3• §aClan bạn đã đạt level tối đa!");
                            return true;
                        }
                        $requiredPoint = $this->getSetting(self::SETTING_REQUIRED_POINT, $nextLevel);
                        $requiredMoney = $this->getSetting(self::SETTING_REQUIRED_MONEY, $nextLevel);
                        $eco = EconomyAPI::getInstance();
                        if (isset($altArgs[0]) && $altArgs[0] === "up") {
                            if ($clan["point"] < $requiredPoint) {
                                $sender->sendMessage("§l§3• §aClan bạn không có đủ điểm Clan để nâng cấp (Cần " . $requiredPoint . ")");
                                return true;
                            }
                            if ($eco->myMoney($sender) < $requiredMoney) {
                                $sender->sendMessage("§l§3• §aBạn không có đủ tiền để nâng cấp Clan (Cần " . $eco->getMonetaryUnit() . $requiredMoney . ")");
                                return true;
                            }
                            $this->clans[$profile->get("clan")]["point"] -= $requiredPoint;
                            $eco->reduceMoney($sender, $requiredMoney);
                            $this->clans[$profile->get("clan")]["level"] = $nextLevel;
                            $this->save();
                            //$eco->save();
                            $sender->sendMessage("§l§3• §aNâng cấp clan thành công!");
                            $this->clanAnnounce("§l§3• §aClan đã được nâng cấp lên cấp $nextLevel. Xin chúc mừng!", $profile->get("clan"));
                            return true;
                        }
                        $sender->sendMessage("§l§3• §aĐể nâng cấp clan lên level $nextLevel cần tiêu hao $requiredPoint điểm clan và " . $eco->getMonetaryUnit() . "$requiredMoney.");
                        $sender->sendMessage("§l§3• §cĐiểm Clan có thể kiếm được bằng cách giết người chơi clan khác (+2) hoặc người chơi bình thường (+1)");
                        $sender->sendMessage("§l§3• §dGõ /clan levelup up để xác nhận nâng cấp clan.");
                        break;
                    case "join":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") !== false) {
                            $sender->sendMessage("§l§3• §aBạn đã có clan.");
                            return true;
                        }
                        if (isset($altArgs[0])) switch ($altArgs[0]) {
                            case "cancel":
                                if (!isset($altArgs[1])) {
                                    $sender->sendMessage("§l§3• §cUsage: /clan join cancel <clanName>");
                                    return true;
                                }
                                if (!isset($this->clans[$altArgs[1]])) {
                                    $sender->sendMessage("§l§3• §aClan không tồn tại.");
                                    return true;
                                }
                                $clan =& $this->clans[$altArgs[1]];
                                if (!in_array($sender->getName(), $clan["request"])) {
                                    $sender->sendMessage("§l§3• §aBạn chưa yêu cầu vào clan này.");
                                    return true;
                                }
                                unset($clan["request"][array_search($sender->getName(), $clan["request"])]);
                                $this->save();
                                $sender->sendMessage("§l§3• §aHủy đơn thành công!");
                                break;
                        }
                        if (!isset($altArgs[0])) {
                            $sender->sendMessage("§l§3• §cUsage: /clan join <clanName>");
                            $sender->sendMessage("§l§3• §cUsage: /clan join cancel <clanName>");
                            return true;
                        }
                        if (!isset($this->clans[$altArgs[0]])) {
                            $sender->sendMessage("§l§3• §aClan " . $altArgs[0] . " không tồn tại.");
                            return true;
                        }
                        if (in_array(strtolower($sender->getName()), $this->clans[$altArgs[0]]["request"])) {
                            $sender->sendMessage("§l§3• §aBạn đã xin vào clan này rồi.");
                            return true;
                        }
                        $this->clans[$altArgs[0]]["request"][] = strtolower($sender->getName());
                        $this->save();
                        $sender->sendMessage("§l§3• §aXin vào clan " . $altArgs[0] . " thành công. Bạn có thể hủy đơn xin vào 1 clan bằng lệnh /clan join cancel");
                        break;
                    case "donate":
                        if (!($sender instanceof Player)) {
                            $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                            return true;
                        }
                        $profile = $this->getProfile($sender);
                        if ($profile->get("clan") === false) {
                            $sender->sendMessage("§l§3• §aBạn không ở trong clan nào.");
                            return true;
                        }
                        $perPointCost = $this->getConfig()->get("point-cost");
                        $eco = EconomyAPI::getInstance();
                        if (!isset($altArgs[0]) || (string)(int)$altArgs[0] !== $altArgs[0]) {
                            $sender->sendMessage("§l§3• §cUsage: /clan donate <point>\n1 point = " . $eco->getMonetaryUnit() . $perPointCost);
                            return true;
                        }
                        $point = (int)$altArgs[0];
                        if ($point <= 0) {
                            $sender->sendMessage("§l§3• §aĐiểm donate phải là một số dương.");
                            return true;
                        }
                        if ($eco->myMoney($sender) - $point * $perPointCost < 0) {
                            $sender->sendMessage("§l§3• §aBạn không đủ tiền (Cần " . ($point * $perPointCost) . ", bạn có " . $eco->myMoney($sender) . ")");
                            return true;
                        }
                        $this->addPoint($point, $this->getClanName($sender));
                        $eco->reduceMoney($sender, $point * $perPointCost);
                        $this->save();
                        $sender->sendMessage("§l§3• §aĐã đóng góp $point điểm.");
                        $this->clanAnnounce($sender->getName() . " đã đóng góp $point điểm.", $this->getClanName($sender));
                        break;
                    default:
                        $sender->sendMessage("§l§3• §aKhông tìm thấy lệnh. Gõ §c/clan help §ađể xem chi tiết.");
                        return true;
                }

                return true;
            case "c":
                if (!($sender instanceof Player)) {
                    $sender->sendMessage("§c Hãy chạy lệnh này trong game");
                    return true;
                }
                $profile = $this->getProfile($sender);
                if ($profile->get("clan") === false) {
                    $sender->sendMessage("§l§3• §aBạn không ở trong clan nào.");
                    return true;
                }
                if (!isset($args[0])) {
                    $sender->sendMessage("§l§3• §cUsage: /c <clan chat message>");
                    return true;
                }
                $this->clanChat($sender, implode(" ", $args));
                return true;
            default:
                return false;
        }
    }

    /**
     * @param Player|string $player
     * @return Config
     */
    public function getProfile($player)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $player = strtolower($player);
        $dir = $this->getDataFolder() . "/profiles/" . substr($player, 0, 1) . "/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $cfg = new Config($dir . "$player.yml", Config::YAML, ["clan" => false, "rank" => 0]);
        return $cfg;
    }

    /**
     * @param int $rank
     * @return string
     */
    public function getRankName(int $rank)
    {
        return $this->getConfig()->get("rank")[$rank]["name"];
    }

    public function save()
    {
        $data = new Config($this->getDataFolder() . "clans.yml", Config::YAML);
        $data->setAll($this->clans);
        $data->save();
    }

    /**
     * @param Player|string $player
     * @param int $action
     * @return bool|null
     */
    public function canDo($player, int $action)
    {
        $profile = $this->getProfile($player);
        if ($profile === null) return null;
        if (($actionString = $this->actionToString($action)) === null) return null;
        return $this->getConfig()->get("rank")[$profile->get("rank")]["control"][$actionString];
    }

    public function actionToString(int $action)
    {
        switch ($action) {
            case self::ACTION_INVITE:
                return "invite";
            case self::ACTION_KICK:
                return "kick";
            case self::ACTION_REQUEST_CONTROL:
                return "requestcontrol";
            case self::ACTION_SET_MOTD:
                return "setmotd";
            case self::ACTION_SET_RANK:
                return "setrank";
            case self::ACTION_LEVEL_UP:
                return "levelup";
        }
        return null;
    }

    /**
     * @param Player|string $player
     * @param string $clanName
     * @return bool
     */
    public function addPlayerToClan($player, string $clanName)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $profile = $this->getProfile($player);
        if (!isset($this->clans[$clanName])) return false;
        $clan =& $this->clans[$clanName];
        if (count($clan["members"]) >= $this->getSetting(self::SETTING_MAX_PLAYERS, $clan["level"])) return false;
        $clan["members"][strtolower($player)] = 1;
        $this->save();
        $profile->set("clan", $clanName);
        $profile->set("rank", 1);
        $profile->save();
        $this->welcomePending[$clanName][] = [$player, []];
        if (($tmp = $this->getServer()->getPlayerExact($player)) !== null) {
            $this->getServer()->getPluginManager()->callEvent(new ClanChangeEvent($tmp, $clanName));
        }
        $this->clanAnnounce($player . " đã vào clan! Gõ /clan welcome để chào mừng!", $clanName);
        return true;
    }

    /**
     * @param Player|string $player
     * @return bool
     */
    public function removePlayerFromClan($player)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $profile = $this->getProfile($player);
        $clanName = $profile->get("clan");
        try {
            unset($this->clans[$clanName]["members"][strtolower($player)]);
        } catch (\Exception $exception) {
            $this->getLogger()->error("Invalid Clan Name");
            return false;
        }
        $this->save();
        $profile->set("clan", false);
        $profile->set("rank", 0);
        $profile->save();
        if (($tmp = $this->getServer()->getPlayerExact($player)) !== null) {
            $this->getServer()->getPluginManager()->callEvent(new ClanChangeEvent($tmp, $clanName));
            $tmp->sendMessage("§l§3• §aBạn đã bị kick khỏi clan.");
        }
        $this->clanAnnounce($player . " đã rời clan.", $clanName);
        return true;
    }

    public function clanAnnounce(string $message, string $clanName)
    {
        $clan = $this->clans[$clanName];
        foreach ($clan["members"] as $name => $rank) {
            $member = $this->getServer()->getPlayerExact($name);
            if (!($member instanceof Player)) continue;
            $member->sendMessage("§l§3•§d " . $message);
        }
    }

    public function clanChat(Player $player, string $message)
    {
        $profile = $this->getProfile($player);
        $clan = $this->clans[$profile->get("clan")];

        /** @var PureChat $pureChat */
        $pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        if ($pureChat === null) return;
        $format = $this->getConfig()->get("clan-chat-format");
        $format .= $message;
        $mes = $pureChat->applyPCTags($format,$player,"",null);
        foreach ($clan["members"] as $name => $rank) {
            $member = $this->getServer()->getPlayerExact($name);
            if (!($member instanceof Player)) continue;
            $member->sendMessage($mes);
        }
    }

    public function setRank($player, int $rank)
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $profile = $this->getProfile($player);
        $clan =& $this->clans[$profile->get("clan")];
        $clan["members"][strtolower($player)] = $rank;
        $this->save();
        $profile->set("rank", $rank);
        $profile->save();
    }

    public function haveClan($player)
    {
        if ($player instanceof Player) $player = $player->getName();
        $profile = $this->getProfile($player);
        return $profile->get("clan") !== false;
    }

    public function getClanName($player)
    {
        $profile = $this->getProfile($player);
        return $profile->get("clan");
    }

    public function getClanTag($player)
    {
        /** @var string $style */
        $style = $this->getSetting(self::SETTING_CLAN_TAG, $this->getClan($this->getClanName($player))["level"]);
        $style = str_replace("%s", $this->getClanName($player), $style);
        return $style;
    }

    public function getClan(string $clanName)
    {
        return $this->clans[$clanName];
    }

    public function isClanFull(string $clanName)
    {
        $clan = $this->getClan($clanName);
        return count($clan["members"]) >= $this->getSetting(self::SETTING_MAX_PLAYERS, $clan["level"]);
    }

    public function getSetting(int $settingId, int $clanLevel)
    {
        switch ($settingId) {
            case self::SETTING_MAX_PLAYERS:
                $key = "max_players";
                break;
            case self::SETTING_REQUIRED_POINT:
                $key = "required_points";
                break;
            case self::SETTING_REQUIRED_MONEY:
                $key = "required_money";
                break;
            case self::SETTING_CLAN_TAG:
                $key = "clan_tag";
                break;
            default:
                return null;
        }
        return $this->settings[$key][$clanLevel - 1];
    }

    public function updateTopClan()
    {
        $topClan = array_keys($this->clans);
        for ($i = 0; $i <= count($topClan) - 2; $i++) {
            for ($j = $i + 1; $j <= count($topClan) - 1; $j++) {
                if ($this->clans[$topClan[$j]]["level"] > $this->clans[$topClan[$i]]["level"] ||
                    ($this->clans[$topClan[$j]]["level"] === $this->clans[$topClan[$i]]["level"] && $this->clans[$topClan[$j]]["point"] > $this->clans[$topClan[$i]]["point"])
                ) {
                    $t = $topClan[$j];
                    $topClan[$j] = $topClan[$i];
                    $topClan[$i] = $t;
                }
            }
        }
        $this->topClan = $topClan;
        $this->getServer()->broadcastMessage("§l§l§3• §aTop Clan đã được cập nhật.");
    }

    public function addPoint(int $point, string $clanName)
    {
        $clan =& $this->clans[$clanName];
        $clan["point"] += $point;
    }

    /**
     * @param string $needle
     * @param string $clanName
     * @param string $except
     * @return null|string
     */
    public function getPlayerInClan(string $needle, string $clanName, string $except = null)
    {
        $clan = $this->getClan($clanName);
        $found = null;
        $needle = strtolower($needle);
        $delta = PHP_INT_MAX;
        foreach ($clan["members"] as $name => $rank) {
            if ($except !== null) {
                if ($name === $except) continue;
            }
            if (stripos($name, $needle) === 0) {
                $curDelta = strlen($name) - strlen($needle);
                if ($curDelta < $delta) {
                    $found = $name;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param string $needle
     * @param string $clanName
     * @param string $except
     * @return null|string
     */
    public function getPlayerInRequestList(string $needle, string $clanName, string $except = null)
    {
        $clan = $this->getClan($clanName);
        $found = null;
        $needle = strtolower($needle);
        $delta = PHP_INT_MAX;
        foreach ($clan["request"] as $name) {
            if ($except !== null) {
                if ($name === $except) continue;
            }
            if (stripos($name, $needle) === 0) {
                $curDelta = strlen($name) - strlen($needle);
                if ($curDelta < $delta) {
                    $found = $name;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

}
