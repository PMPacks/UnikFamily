<?php

namespace staff;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

    public $interactDelay = [];
    public $targetPlayer = [];
    public $staffList = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->db = new \SQLite3($this->getDataFolder() . "StaffTool.db");
		$this->db->exec("CREATE TABLE IF NOT EXISTS banPlayers(player TEXT PRIMARY KEY, banTime INT, reason TEXT, staff TEXT);");
        $this->db->exec("CREATE TABLE IF NOT EXISTS kickPlayers(player TEXT PRIMARY KEY, reason TEXT, staff TEXT);");
		$this->message = (new Config($this->getDataFolder() . "config.yml", Config::YAML, array(
			
            "ServerBroadcastBanMessage" => "§6§l【 §aмιɴᴇ§bsтoɴᴇ §6】§c{player} §eđã bị khóa tài khoản bởi §b{staff} §eDo §6{reason}",
            
			"KickBanMessage" => "§6§l【 §aмιɴᴇ§bsтoɴᴇ §6】§cBạn đã bị ban bởi §b{staff} §ctrong §6{day} ngày, {hour} tiếng, {minute} phút §cdo §6{reason}§c \nBạn có thể kích hoạt tài khoản tại §bhttps://minestoneweb.tk/sv",
			
            "KickMessage" => "§6§l【 §aмιɴᴇ§bsтoɴᴇ §6】§cBạn đã bị kick bởi §b{staff} §cdo §6{reason}§c",
			
            "LoginBanMessage" => "§6§l【 §aмιɴᴇ§bsтoɴᴇ §6】§cTài khoản đã bị khóa bởi §b{staff} §cdo §6{reason} \n§cTài khoản sẽ mở lại sau §6{day} ngày, {hour} giờ, {minute} phút",
            
            "PlayerUnban" => "§6{player}§a Đã được mở khóa",
			
            "PlayerUnmute" => "§6{player}§a đã được mở chat!",
            
            "AutoRemovePunishment" => "§6{player}§a đã được mở khóa",
			
            "NoPunishedPlayers" => "§cNo Punishments logged",
			
			"PlayerKickSuccess" => "§eYou have kicked §6{player}§e for §c{reason}",
			
			"PlayerBanSuccess" => "§eBạn đã bị khóa tài khoản trong §6{player}§e trong §b{day} ngày, {hour} giờ, {minute} phút §edo §c{reason}",
			
			"ServerBroadcastKickMessage" => "§c{player} §ehas been kicked by §b{staff} §efor §6{reason}",
			
            "PunishmentMuteInfo" => "§6Punishment: §eMute \n§6Reason: §e{reason} \n§6Punished by: §e{staff} \n§6Expires in: \n§e{day} Days §c| §e{hour} Hours §c| §e{minute} Minutes",
            
			"PunishmentBanInfo" => "§6Punishment: §eBan \n§6Reason: §e{reason} \n§6Punished by: §e{staff} \n§6Expires in: \n§e{day} Days §c| §e{hour} Hours §c| §e{minute} Minutes",
		)))->getAll();
    }


    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        if($cmd->getName() === "stafftable"){
            if(count($args) === 0){
                if($sender->hasPermission("stafftable.command")){
                    if($sender instanceof Player){
                        $this->openStaffTable($sender);
                    }
                }
            } else {
                if(count($args) === 1){
                    $this->targetPlayer[$sender->getName()] = $args[0];
                    $this->openPlayerAction($sender);
                }
			}
        }
	return true;
	}

    public function openStaffTable($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, $data = null){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    $this->openPlayerList($player);
				break;
				
				case 1:
                    $this->openPunishedPlayers($player);
                break;
            }
        });
        $form->setTitle("§c-= §6Staff Tool §c=-");
        $form->setContent("§eSelect an option");
		$form->addButton("Player List");
		$form->addButton("Punished Players");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openPlayerList($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, $data = null){
			$target = $data;
			if($target === null){
				return true;
			}
			$this->targetPlayer[$player->getName()] = $target;
			$this->openPlayerAction($player);
		});
		$form->setTitle("§6Online Players");
		$form->setContent("§eChoose a player");
		foreach($this->getServer()->getOnlinePlayers() as $online){
			$form->addButton($online->getName(), -1, "", $online->getName());
		}
		$form->sendToPlayer($player);
		return $form;
    }

    public function openPlayerAction($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null){
            $result = $data;
            if($result === null){
                return true;
            }
            switch($result){
                case 0:
                    $this->openBanForm($player);
                break;

                case 1:
                    $this->openKickForm($player);
				break;
            }
        });
        $form->setTitle("§6Action Form §a(§e" . $this->targetPlayer[$player->getName()] . "§a)");
        $form->addButton("Ban");
        $form->addButton("Kick");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openBanForm($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(function (Player $player, array $data = null){
			$result = $data[0];
			if($result === null){
				return true;
			}
			if(isset($this->targetPlayer[$player->getName()])){
				
				$now = time();
				$day = ($data[1] * 86400);
				$hour = ($data[2] * 3600);
				if($data[3] > 1){
					$min = ($data[3] * 60);
				} else {
					$min = 60;
				}
				$banTime = $now + $day + $hour + $min;
				$banInfo = $this->db->prepare("INSERT OR REPLACE INTO banPlayers (player, banTime, reason, staff) VALUES (:player, :banTime, :reason, :staff);");
				$banInfo->bindValue(":player", $this->targetPlayer[$player->getName()]);
				$banInfo->bindValue(":banTime", $banTime);
				$banInfo->bindValue(":reason", $data[4]);
                $banInfo->bindValue(":staff", $player->getName());
				$banInfo->execute();
				$target = $this->getServer()->getPlayerExact($this->targetPlayer[$player->getName()]);
				if($target instanceof Player){
					$target->close("", str_replace(["{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$data[1], $data[2], $data[3], $data[4], $player->getName()], $this->message["KickBanMessage"]));
				}
				$player->sendMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}"], [$this->targetPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4]], $this->message["PlayerBanSuccess"]));
				$this->getServer()->broadcastMessage(str_replace(["{player}", "{day}", "{hour}", "{minute}", "{reason}", "{staff}"], [$this->targetPlayer[$player->getName()], $data[1], $data[2], $data[3], $data[4], $player->getName()], $this->message["ServerBroadcastBanMessage"]));
				unset($this->targetPlayer[$player->getName()]);

			}
		});
		$list[] = $this->targetPlayer[$player->getName()];
        $form->setTitle("§l§cBan Tool§r§6 (§b" . $this->targetPlayer[$player->getName()] . "§6)");
        $form->addDropdown("§6Player", $list);
		$form->addSlider("§6Days", 0, 45, 1);
		$form->addSlider("§6Hours", 0, 24, 1);
		$form->addSlider("§6Minutes", 0, 60, 5);
		$form->addInput("§6Reason");
		$form->sendToPlayer($player);
		return $form;
    }
    
    public function onPlayerLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$banplayer = $player->getName();
		$banInfo = $this->db->query("SELECT * FROM banPlayers WHERE player = '$banplayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		if (!empty($array)) {
			$banTime = $array['banTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			if($banTime > $now){
				$remainingTime = $banTime - $now;
				$day = floor($remainingTime / 86400);
				$hourSeconds = $remainingTime % 86400;
				$hour = floor($hourSeconds / 3600);
				$minuteSec = $hourSeconds % 3600;
				$minute = floor($minuteSec / 60);
				$remainingSec = $minuteSec % 60;
				$second = ceil($remainingSec);
				$player->close("", str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], $this->message["LoginBanMessage"]));
			} else {
				$this->db->query("DELETE FROM banPlayers WHERE player = '$banplayer';");
			}
		}
		if(isset($this->staffList[$player->getName()])){
			unset($this->staffList[$player->getName()]);
		}
    }

    public function openKickForm($player){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createCustomForm(function (Player $player, array $data = null){
			$result = $data[0];
			if($result === null){
				return true;
			}
            if(isset($this->targetPlayer[$player->getName()])){
                $kickInfo = $this->db->prepare("INSERT OR REPLACE INTO kickPlayers (player, reason, staff) VALUES (:player, :reason, :staff);");
                $kickInfo->bindValue(":player", $this->targetPlayer[$player->getName()]);
                $kickInfo->bindValue(":reason", $data[1]);
				$kickInfo->bindValue(":staff", $player->getName());
                $kickInfo->execute();
                $target = $this->getServer()->getPlayerExact($this->targetPlayer[$player->getName()]);
				if($target instanceof Player){
                    $target->close("", str_replace(["{reason}", "{staff}"], [$data[1], $player->getName()], $this->message["KickMessage"]));
                }
				$player->sendMessage(str_replace(["{player}", "{reason}"], [$this->targetPlayer[$player->getName()], $data[1], $player->getName()], $this->message["PlayerKickSuccess"])); 
				$this->getServer()->broadcastMessage(str_replace(["{player}", "{reason}", "{staff}"], [$this->targetPlayer[$player->getName()], $data[1], $player->getName()], $this->message["ServerBroadcastKickMessage"]));
                unset($this->targetPlayer[$player->getName()]);
            }
        });
        $list[] = $this->targetPlayer[$player->getName()];
        $form->setTitle("§l§cKick Tool§r§6 (§b" . $this->targetPlayer[$player->getName()] . "§6)");
        $form->addDropdown("§6Player", $list);
        $form->addInput("§6Reason");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openPunishmentInfo($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
		$result = $data;
		if($result === null){
			return true;
		}
			switch($result){
				case 0:
					$bannedplayer = $this->targetPlayer[$player->getName()];
					$banInfo = $this->db->query("SELECT * FROM banPlayers WHERE player = '$bannedplayer';");
					$array = $banInfo->fetchArray(SQLITE3_ASSOC);
					if (!empty($array)) {
						$this->db->query("DELETE FROM banPlayers WHERE player = '$bannedplayer';");
						$player->sendMessage(str_replace(["{player}"], [$bannedplayer], $this->message["PlayerUnban"]));
					}
					unset($this->targetPlayer[$player->getName()]);
				break;
			}
		});
		$bannedPlayer = $this->targetPlayer[$player->getName()];
		$banInfo = $this->db->query("SELECT * FROM banPlayers WHERE player = '$bannedPlayer';");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);
		if (!empty($array)) {
			$banTime = $array['banTime'];
			$reason = $array['reason'];
			$staff = $array['staff'];
			$now = time();
			if($banTime < $now){
				$bannedplayer = $this->targetPlayer[$player->getName()];
				$banInfo = $this->db->query("SELECT * FROM banPlayers WHERE player = '$bannedplayer';");
				$array = $banInfo->fetchArray(SQLITE3_ASSOC);
				if (!empty($array)) {
					$this->db->query("DELETE FROM banPlayers WHERE player = '$bannedplayer';");
					$player->sendMessage(str_replace(["{player}"], [$bannedplayer], $this->message["AutoRemovePunishment"]));
				}
				unset($this->targetPlayer[$player->getName()]);
				return true;
			}
			$remainingTime = $banTime - $now;
			$day = floor($remainingTime / 86400);
			$hourSeconds = $remainingTime % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$remainingSec = $minuteSec % 60;
			$second = ceil($remainingSec);
		}
		$form->setTitle("§6Info for §e" . $this->targetPlayer[$player->getName()]);
		$form->setContent(str_replace(["{day}", "{hour}", "{minute}", "{second}", "{reason}", "{staff}"], [$day, $hour, $minute, $second, $reason, $staff], $this->message["PunishmentBanInfo"]));
		$form->addButton("Unban");
		$form->sendToPlayer($player);
		return $form;
    }

    public function openPunishedPlayers($player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, $data = null){
			if($data === null){
				return true;
			}
			$this->targetPlayer[$player->getName()] = $data;
			$this->openPunishmentInfo($player);
		});
		$banInfo = $this->db->query("SELECT * FROM banPlayers;");
		$array = $banInfo->fetchArray(SQLITE3_ASSOC);	
		if (empty($array)) {
			$player->sendMessage($this->message["NoPunishedPlayers"]);
			return true;
		}
		$form->setTitle("§l§cPunished Players");
		$form->setContent("§6Click on a player to see more infos");
		$banInfo = $this->db->query("SELECT * FROM banPlayers;");
		$i = -1;
		while ($resultArr = $banInfo->fetchArray(SQLITE3_ASSOC)) {
			$j = $i + 1;
			$banPlayer = $resultArr['player'];
			$form->addButton("$banPlayer", -1, "", $banPlayer);
			$i = $i + 1;
		}
		$form->sendToPlayer($player);
		return $form;
	}
}