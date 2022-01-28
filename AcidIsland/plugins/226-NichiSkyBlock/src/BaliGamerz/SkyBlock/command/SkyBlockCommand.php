<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\command;


use BaliGamerz\SkyBlock\Menu\PublicMenu;
use BaliGamerz\SkyBlock\Menu\ShopLogic;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\entity\Skin;
use BaliGamerz\SkyBlock\invitation\Invitation;
use BaliGamerz\SkyBlock\island\Island;
use BaliGamerz\SkyBlock\Main;

class SkyBlockCommand extends Command
{
    /** @var Main */
    private $plugin;

    /**
     * SkyBlockBlockCommand constructor.
     *
     * @param Main $plugin
     */
    public function __construct(string $name, Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($name);
        $this->setDescription('Main SkyBlock command');
        $this->setUsage('Usage: /skyblock');
        $this->setAliases(['sb', 'is']);
    }

    public function sendMessage($sender, $message)
    {
        $sender->sendMessage("§f[§aSKYBLOCK§f] §f» §b".$message);
    }

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "score":
                        $name = strtolower($sender->getName());
                        if(isset($this->plugin->disablePlayer[$name])){
                            unset($this->plugin->disablePlayer[$name]);
                            $sender->sendMessage("§aĐã bật thành công ScoreHud.");
                        }else{
                            $this->plugin->disablePlayer[$name] = 1;
                            $this->plugin->getScore()->unBuild($sender);
                            $sender->sendMessage("§cĐã vô hiệu hóa thành công ScoreHud.");
                        }
                        break;
                    case "setting":
                        if($sender->hasPermission("sbpe.edit")) {
                            (new PublicMenu())->settingForm($sender);
                        }else{
                            $this->sendMessage($sender, "Bạn không thể sử dụng lệnh này");
                        }
                        break;
                    case "shop":
                        (new ShopLogic())->onOpen($sender);
                        break;
                    case "home":
                        if ($sender->hasPermission('sbpe.cmd.home')) {
                            $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                            if (empty($config["island"])) {
                                $this->sendMessage($sender, "You haven't a island!");
                            } else {
                                $island = $this->plugin->getIslandManager()->getOnlineIsland($config["island"]);
                                if ($island instanceof Island) {
                                    $home = $island->getHomePosition();
                                    if ($home instanceof Position) {
                                        $sender->teleport($home);
                                        $this->sendMessage($sender, "Bạn đã được dịch chuyển đến hòn đảo quê hương của bạn ");
                                    } else {
                                        $this->sendMessage($sender, "Đảo của bạn chưa được đặt vị trí nhà!");
                                    }
                                } else {
                                    $this->sendMessage($sender, "You haven't a island!!");
                                }
                            }
                        }
                        break;
                    case "sethome":
                        if ($sender->hasPermission('sbpe.cmd.sethome')) {
                            $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                            if (empty($config["island"])) {
                                $this->sendMessage($sender, "You haven't a island!");
                            } else {
                                $island = $this->plugin->getIslandManager()->getOnlineIsland($config["island"]);
                                if ($island instanceof Island) {
                                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                                        if ($sender->getLevel()->getName() == $config["island"]) {
                                            $island->setHomePosition($sender->getPosition());
                                            $this->sendMessage($sender, "Bạn đã đặt thành công hòn đảo của mình!");
                                        } else {
                                            $this->sendMessage($sender, "Bạn phải ở hòn đảo của bạn để trở về nhà!");
                                        }
                                    } else {
                                        $this->sendMessage($sender, "Bạn phải là người lãnh đạo hòn đảo để làm điều này!");
                                    }
                                } else {
                                    $this->sendMessage($sender, "You haven't a island!!");
                                }
                            }
                        }
                    case "accept":
                        if ($sender->hasPermission('sbpe.cmd.invite.accept')) {
                            if (isset($args[1])) {
                                $config = $this->plugin->getSkyBlockManager()->getPlayerConfig($sender);
                                if (empty($config["island"])) {
                                    $player = $this->plugin->getServer()->getPlayer($args[1]);
                                    if ($player instanceof Player and $player->isOnline()) {
                                        $invitation = $this->plugin->getInvitationHandler()->getInvitation($player);
                                        if ($invitation instanceof Invitation) {
                                            if ($invitation->getSender() == $player) {
                                                $invitation->accept();
                                            } else {
                                                $this->sendMessage($sender, "You haven't a invitation from {$player->getName()}!");
                                            }
                                        } else {
                                            $this->sendMessage($sender, "Bạn không có lời mời từ {$player->getName()}");
                                        }
                                    } else {
                                        $this->sendMessage($sender, "{$args[1]} không phải là một người chơi hợp lệ");
                                    }
                                } else {
                                    $this->sendMessage($sender, "Bạn không thể ở một hòn đảo nếu bạn muốn tham gia một hòn đảo khác!");
                                }
                            } else {
                                $this->sendMessage($sender, "Usage: /SkyBlockBlock accept <sender name>");
                            }
                        }
                        break;
                    case "reload":
                        $this->sendMessage($sender, "Reloading Users.yml");
                        $this->sendMessage($sender, "Reloading Islands");
                        $this->sendMessage($sender, "Reloading Config.yml");
                        $this->sendMessage($sender, "Reloading data.yml");
                        $this->plugin->scoreReload();
                        $this->plugin->getIslandManager()->updateDisableIslandServer();
                        break;
                    default:
                        $ui = new PublicMenu();
                        $ui->onMenu($sender);
                        break;
                }
            } else {
                $ui = new PublicMenu();
                $ui->onMenu($sender);
            }
        } else {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "reload":
                        $this->sendMessage($sender, "Reloading Users.yml");
                        $this->sendMessage($sender, "Reloading Config.yml");
                        $this->sendMessage($sender, "Reloading Islands");
                        $this->sendMessage($sender, "Reloading data.yml");
                        $this->plugin->scoreReload();
                        $this->plugin->getIslandManager()->updateDisableIslandServer();
                        break;
                    default:
                        $this->sendMessage($sender, "Use /sb reload to reloading data!");
                        break;
                }
            } else {
                $this->sendMessage($sender, "Use /sb reload to reloading data!");
            }
        }
    }
}
