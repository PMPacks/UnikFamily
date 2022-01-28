<?php

namespace tungsten3;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\{Command, CommandSender, CommandExecutor, ConsoleCommandSender};

use pocketmine\Player;
use pocketmine\event\player\PlayerChatEvent as Chat;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener
{


    private $task;

	public $thisserver = "SkyCoop";
	public $prefix = "§f[{server}§f]§r ";

    public $connect;
    public $list = array();
    public $players = [];
    public $purechat;
    public $purePerms;

    public function onEnable()
    {
        $this->purechat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        $this->purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        if ($this->purechat == null or $this->purePerms == null) {
            $this->getLogger()->info("\n\nthiếu thư viện purechat/pureperm\n\n");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->task = new repeatingTask($this);
        $this->getScheduler()->scheduleRepeatingTask($this->task, 0);
        $this->connect = new connectMysql($this);

    }

    public function PlayerChatMessage(Chat $e)
    {
        $name = $e->getPlayer()->getName();
        $msg = $e->getMessage();
		$sv =  $this->thisserver;
		$insert = "INSERT INTO Lobby (server,name,message) VALUES ('$sv','$name','$msg')";
		if (!$this->connect->db->query($insert)) {
            $this->getLogger()->info("Error: " . $this->connect->db->error);
        }
    }
    public function ShowChat()
    {
        $result = $this->connect->db->query("SELECT * FROM " . $this->thisserver);
        if (!$result) {
            print($this->connect->db->error);
        } else {
            while ($row = $result->fetch_assoc()) {
                $name = str_replace(" ", "_", $row["name"]);
                if (!isset($this->players[$name])) {
                    $this->players[$name] = $this->getServer()->getOfflinePlayer($name);
                }
                $surplayerObject = $this->players[$name];
                $message = $row["message"];
                $chatFormat = $this->purechat->getChatFormat($surplayerObject, $message, null);
                $chatFormat = str_replace("{server}",$row["server"],$this->prefix) . $chatFormat;
				$this->getServer()->broadcastMessage($chatFormat);
                $del = $this->connect->db->query("DELETE FROM " . $this->thisserver . " WHERE id = " . $row["id"]);
                if (!$del) {
                    print($this->connect->db->error);
                }
            }
        }
    }
    public function onJoin(PlayerJoinEvent $ev)
    {
        $this->players[$ev->getPlayer()->getName()] = $ev->getPlayer();
    }

}