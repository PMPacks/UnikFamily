<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\FunctionLogic;


use BaliGamerz\SkyBlock\events\LeaderboardNpcCreateEvent;
use BaliGamerz\SkyBlock\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\level\Position;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use BaliGamerz\SkyBlock\invitation\Invitation;
use BaliGamerz\SkyBlock\island\Island;
use BaliGamerz\SkyBlock\Main;

class ListFunction
{

    #Plugin Var
    public function getPlugin(): ?Main
    {
        return Main::getInstance();
    }

    #Message
    public function sendMessage(Player $sender, $message)
    {
        $sender->sendMessage("§f[§aSKYBLOCK§f] §f» §b" . $message);
    }


    #Function Join
    public function joinFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.join')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "Bạn không có một hòn đảo!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    $server = $this->getPlugin()->getServer();
                    if (!$server->isLevelLoaded($island->getIdentifier())) {
                        $server->loadLevel($island->getIdentifier());
                    }
                    $sender->teleport(new Position($island->getPosition()->x, $island->getPosition()->y + 2, $island->getPosition()->z, $this->getPlugin()->getServer()->getLevelByName($island->getIdentifier())));
                    $this->sendMessage($sender, "Bạn đã dịch chuyển về đảo");
                } else {
                    $this->sendMessage($sender, "You haven't a island!!");
                }
            }
        }
    }

    #Function Create
    public function createFunction(Player $sender, $name, $array): bool
    {
        if ($sender->hasPermission('sbpe.cmd.create')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $reset = $config['last-creation'];
                    if (($reset - time()) > 1) {
                        $minutes = Utils::printSeconds($reset - time());
                        if(!$sender->hasPermission("sbpe.countdown.bypass")) {
                            $this->sendMessage($sender, "Bạn sẽ có thể tạo một hòn đảo mới sau {$minutes} phút");
                            return false;
                        }
                    }
                    $this->getPlugin()->getSkyBlockManager()->generateIsland($sender, $name, $array);
                    return true;
            } else {
                $this->sendMessage($sender, "Bạn đã có một hòn đảo!");
                return false;
            }
        }
        return false;
    }

    #Function Home
    public function homeFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.home')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "You haven't a island!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    $home = $island->getHomePosition();
                    if ($home !== null) {
                        $sender->teleport($home);
                        $this->sendMessage($sender, "Bạn đã được dịch chuyển đến hòn đảo quê hương của bạn");
                    } else {
                        $this->sendMessage($sender, "Đảo của bạn chưa được đặt vị trí nhà!");
                    }
                } else {
                    $this->sendMessage($sender, "You haven't a island!!");
                }
            }
        }
    }

    #Function setHome
    public function setHomeFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.sethome')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "You haven't a island!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        if ($sender->getLevel()->getName() == $config["island"]) {
                            $island->setHomePosition($sender->getPosition());
                            $this->sendMessage($sender, "Bạn đã đặt thành công hòn đảo của mình!");
                        } else {
                            $this->sendMessage($sender, "Bạn phải ở hòn đảo của bạn để trở về nhà!");
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn phải là người đứng đầu hòn đảo để làm thí nghiệm!");
                    }
                } else {
                    $this->sendMessage($sender, "You haven't a island!!");
                }
            }
        }
    }

    #Function Kick
    public function kickFunction($sender, $target)
    {
        if ($sender->hasPermission('sbpe.cmd.kick')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "You haven't a island!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        $player = $this->getPlugin()->getServer()->getPlayer($target);
                        if ($player instanceof Player and $player->isOnline()) {
                            if ($player->getLevel()->getName() == $island->getIdentifier()) {
                                $player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
                                $this->sendMessage($sender, "{$player->getName()} đã bị đá khỏi hòn đảo của bạn!");
                            } else {
                                $this->sendMessage($sender, "Người chơi không ở đảo của bạn!");
                            }
                        } else {
                            $this->sendMessage($sender, "Đó không phải là một người chơi hợp lệ");
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn phải chủ sở hữu hòn đảo để trục xuất anySkyBlock");
                    }
                } else {
                    $this->sendMessage($sender, "You haven't a island!");
                }
            }
        }
    }

    #Function Lock
    public function lockFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.lock')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "You haven't a island!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        $island->setLocked(!$island->isLocked());
                        $locked = ($island->isLocked()) ? "locked" : "unlocked";
                        $this->sendMessage($sender, "Trạng Thái: {$locked}!");
                    } else {
                        $this->sendMessage($sender, "Bạn phải là chủ nhân của hòn đảo để làm thí dụ!");
                    }
                } else {
                    $this->sendMessage($sender, "You haven't a island!");
                }
            }
        }
    }

    #Function Invite
    public function inviteFunction($sender, $target)
    {
        if ($sender->hasPermission('sbpe.cmd.invite')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "You haven't a island!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        if (count($island->getMembers()) < (int)$this->getPlugin()->config['island']['max_members']) {
                            $player = $this->getPlugin()->getServer()->getPlayer($target);
                            if ($player instanceof Player and $player->isOnline()) {
                                $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($player);
                                if (empty($config["island"])) {
                                    $this->getPlugin()->getInvitationHandler()->addInvitation($sender, $player, $island);
                                    $this->sendMessage($sender, "Bạn đã gửi lời mời tới {$player->getName()}!");
                                    $this->sendMessage($player, "{$sender->getName()} đã mời bạn đến hòn đảo của họ! Làm /sb <accept/reject> {$sender->getName()}");
                                } else {
                                    $this->sendMessage($sender, "Người chơi này đã ở trên một hòn đảo!");
                                }
                            } else {
                                $this->sendMessage($sender, "{$target} không phải là một người chơi hợp lệ!");
                            }
                        } else {
                            $this->sendMessage($sender, "Thành viên của bạn đầy đủ, Thành viên tối đa: " . $this->getPlugin()->config['island']['max_members']);
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn phải là chủ nhân của hòn đảo để làm thí dụ!");
                    }
                } else {
                    $this->sendMessage($sender, "You haven't a island!!");
                }
            }
        }
    }

    #Accept Function
    public function acceptFunction($sender, $target)
    {
        if ($sender->hasPermission('sbpe.cmd.invite.accept')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $player = $this->getPlugin()->getServer()->getPlayer($target);
                if ($player instanceof Player and $player->isOnline()) {
                    $invitation = $this->getPlugin()->getInvitationHandler()->getInvitation($player);
                    if ($invitation instanceof Invitation) {
                        if ($invitation->getSender()->getName() == $player->getName()) {
                            if ($invitation->getTime() > 0) {
                                $invitation->accept();
                            } else {
                                $invitation->expire();
                                $this->sendMessage($sender, "Lời mời này đã hết hạn!, vui lòng liên hệ với chủ sở hữu để mời");
                            }
                        } else {
                            $this->sendMessage($sender, "Bạn không có lời mời từ {$player->getName()}!");
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn không có lời mời từ {$player->getName()}");
                    }
                } else {
                    $this->sendMessage($sender, "{$target} không phải là một người chơi hợp lệ");
                }
            } else {
                $this->sendMessage($sender, "Bạn không thể ở một hòn đảo nếu bạn muốn tham gia một hòn đảo khác!");
            }
        }
    }

    #Reject Function
    public function rejectFunction($sender, $target)
    {
        if ($sender->hasPermission('sbpe.cmd.invite.deny')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $player = $this->getPlugin()->getServer()->getPlayer($target);
                if ($player instanceof Player and $player->isOnline()) {
                    $invitation = $this->getPlugin()->getInvitationHandler()->getInvitation($player);
                    if ($invitation instanceof Invitation) {
                        if ($invitation->getSender()->getName() == $player->getName()) {
                            $invitation->deny();
                        } else {
                            $this->sendMessage($sender, "Bạn không có lời mời từ {$player->getName()}!");
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn không có lời mời từ {$player->getName()}");
                    }
                } else {
                    $this->sendMessage($sender, "{$target} không phải là một người chơi hợp lệ");
                }
            } else {
                $this->sendMessage($sender, "Bạn không thể ở một hòn đảo nếu bạn muốn từ chối một hòn đảo khác!");
            }
        }
    }

    #Disband Function
    public function disbandFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.remove')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "Bạn phải ở một hòn đảo để giải tán nó!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        foreach ($island->getAllMembers() as $member) {
                            $this->getPlugin()->playerDataPath[strtolower($member)] = Utils::getFormatPlayerDataPath();
                            $memberObject = $this->getPlugin()->getServer()->getPlayer($member);
                            $this->getPlugin()->economy->addMoney($memberObject, $island->getShareMoney());
                        }
                        $this->getPlugin()->getIslandManager()->removeIsland($island);
                        $this->getPlugin()->playerDataPath[strtolower($sender->getName())]['last-creation'] = time() + $this->getPlugin()->config['island']['creation-time'];
                        $this->sendMessage($sender, "You successfully deleted the island!");
                    } else {
                        $this->sendMessage($sender, "Bạn phải là chủ sở hữu để giải tán hòn đảo!");
                    }
                } else {
                    $this->sendMessage($sender, "Bạn phải ở trong một hòn đảo để giải tán nó !!");
                }
            }
        }
    }


    /**
     * @param $sender
     * @return CompoundTag
     */
    public function getSkinTag($sender): CompoundTag
    {
        $path = $this->getPlugin()->getDataFolder() . "Skin/leaderboard.png";
        $img = @imagecreatefrompng($path);
        $skinbytes = "";
        $s = @getimagesize($path);
        for ($y = 0; $y < $s[1]; $y++) {
            for ($x = 0; $x < $s[0]; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        $skinTag = new CompoundTag("Skin", [
            "Name" => new StringTag("Name", $sender->getSkin()->getSkinId()),
            "Data" => new ByteArrayTag("Data", $skinbytes),
            "GeometryName" => new StringTag("GeometryName", "geometry.leaderboard"),
            "GeometryData" => new ByteArrayTag("GeometryData", file_get_contents($this->getPlugin()->getDataFolder() . "Skin/leaderboard.json"))
        ]);
        return $skinTag;
    }

    public function createLeaderBoard(Player $player, $name, $leaderboard = true, $boundingBox = 2.5)
    {

        $nbt = Entity::createBaseNBT($player, null, $player->getYaw(), $player->getPitch());
        $nbt->setShort("Health", 1);
        $nbt->setString("MenuName", "");
        $nbt->setString("CustomName", $name);
        $nbt->setFloat("height", $boundingBox);
        if ($leaderboard) {
            $nbt->setFloat("leaderboard", 1.0);
            $nbt->setTag($this->getSkinTag($player));
        } else {
            $nbt->setFloat("npcStats", 1.0);
            $nbt->setTag(clone $player->namedtag->getCompoundTag('Skin'));
        }
        $player->saveNBT();
        $human = Entity::createEntity("NpcClass", $player->level, $nbt);
        if ($leaderboard) {
            (new LeaderboardNpcCreateEvent($human))->call();
        }
        $human->spawnToAll();
    }

    #Makeleader Function
    public function makeleaderFunction($sender, $target)
    {
        if ($sender->hasPermission('sbpe.cmd.makeleader')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "Bạn phải ở một hòn đảo để thiết lập một nhà lãnh đạo mới!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        $player = $this->getPlugin()->getServer()->getPlayer($target);
                        if ($player instanceof Player and $player->isOnline()) {
                            $playerConfig = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($player);
                            $playerIsland = $this->getPlugin()->getIslandManager()->getOnlineIsland($playerConfig["island"]);
                            if ($island->getIdentifier() == $playerIsland->getIdentifier()) {
                                $island->setOwnerName($player);
                                $this->sendMessage($sender, "Bạn đã gửi quyền sở hữu cho {$player->getName()}");
                                $this->sendMessage($player, "You get your island ownership by {$sender->getName()}");
                            } else {
                                $this->sendMessage($sender, "Người chơi phải ở trên hòn đảo của bạn!");
                            }
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn phải là người đứng đầu hòn đảo để làm thí nghiệm!");
                    }
                } else {
                    $this->sendMessage($sender, "Bạn phải ở một hòn đảo để thiết lập một nhà lãnh đạo mới !!");
                }
            }
        }
    }

    #Leave Function
    public function leaveFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.leave')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "Bạn phải ở một hòn đảo để rời khỏi nó!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        $this->sendMessage($sender, "Bạn không thể rời khỏi một hòn đảo nếu chủ sở hữu của bạn! Có thể bạn có thể thử sử dụng /sb disband");
                    } else {
                        $this->getPlugin()->playerDataPath[strtolower($sender->getName())] = Utils::getFormatPlayerDataPath();
                        $island->removeMember(strtolower($sender->getName()));
                        $this->sendMessage($sender, "Bạn đã rời khỏi hòn đảo!!");
                    }
                } else {
                    $this->sendMessage($sender, "Bạn phải ở trong một hòn đảo để rời khỏi nó!!");
                }
            }
        }
    }

    #Remove Function
    public function removeFunction($sender, $target)
    {
        if ($sender->hasPermission('sbpe.cmd.remove')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "Bạn phải ở trong một hòn đảo để rời khỏi nó!");
            } else {
                $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                if ($island instanceof Island) {
                    if ($island->getOwnerName() == strtolower($sender->getName())) {
                        if (in_array(strtolower($target), $island->getMembers())) {
                            $island->removeMember(strtolower($target));
                            $this->getPlugin()->economy->addMoney($target, $island->getShareMoney());
                            $this->getPlugin()->playerDataPath[strtolower($target)] = Utils::getFormatPlayerDataPath();
                            $this->sendMessage($sender, "{$target} đã bị xóa khỏi nhóm của bạn!");
                        } else {
                            $this->sendMessage($sender, "{$target} không phải là người chơi của hòn đảo của bạn!");
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn phải là chủ nhân của hòn đảo để làm thí dụ!");
                    }
                } else {
                    $this->sendMessage($sender, "Bạn phải ở trong một hòn đảo để rời khỏi nó !!");
                }
            }
        }
    }

    #Tp Function
    public function tpFunction(Player $sender, Player $target)
    {
        if ($sender->hasPermission('sbpe.cmd.tp')) {
            $island = $this->getPlugin()->getIslandManager()->getIslandByPlayer($target);
            if ($island instanceof Island) {
                if ($island->isLocked()) {
                    $this->sendMessage($sender, "Hòn đảo này bị khóa, bạn không thể tham gia nó!");
                } else {
                    $sender->teleport(new Position($island->getPosition()->x, $island->getPosition()->y + 2, $island->getPosition()->z, $this->getPlugin()->getServer()->getLevelByName($island->getIdentifier())));
                    $this->sendMessage($sender, "Bạn đã tham gia đảo thành công");
                }
            } else {
                $this->sendMessage($sender, "Ít nhất thành viên trên đảo SkyBlock phải hoạt động nếu bạn muốn nhìn thấy hòn đảo!");
            }
        }
    }

    #Reset Function
    public function resetFunction($sender)
    {
        if ($sender->hasPermission('sbpe.cmd.reset')) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
            if (empty($config["island"])) {
                $this->sendMessage($sender, "Bạn phải ở một hòn đảo để thiết lập lại nó!");
            } else {
                $reset = $config['last-creation'];
                if (($reset - time()) > 1) {
                    $minutes = Utils::printSeconds($reset - time());
                    $this->sendMessage($sender, "Bạn sẽ có thể đặt lại một cái mới sau {$minutes} phút nữa");
                } else {
                    $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
                    if ($island instanceof Island) {
                        if ($island->getOwnerName() == strtolower($sender->getName())) {
                            foreach ($island->getAllMembers() as $member) {
                                $this->getPlugin()->playerDataPath[strtolower($member)] = Utils::getFormatPlayerDataPath();
                                $memberObject = $this->getPlugin()->getServer()->getPlayer($member);
                                $this->getPlugin()->economy->addMoney($memberObject, $island->getShareMoney());
                            }
                            $name = $island->getIdentifier();
                            $generator = $island->getGenerator();
                            $this->getPlugin()->getIslandManager()->removeIsland($island);
                            $this->getPlugin()->getSkyBlockManager()->generateIsland($sender, $name, $this->getPlugin()->dataThemas['list'][$generator]);
                            $this->getPlugin()->playerDataPath[strtolower($sender->getName())]['last-creation'] = time() + $this->getPlugin()->config['island']['creation-time'];
                            $this->sendMessage($sender, "Bạn đã đặt lại đảo thành công!");
                        } else {
                            $this->sendMessage($sender, "Bạn phải là chủ sở hữu để thiết lập lại hòn đảo!");
                        }
                    } else {
                        $this->sendMessage($sender, "Bạn phải ở một hòn đảo để thiết lập lại nó!!");
                    }
                }
            }
        }
    }
}