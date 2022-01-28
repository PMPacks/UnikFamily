<?php

	namespace AlexBrin;

	use AlexBrin\Main;
	use pocketmine\command\ConsoleCommandSender;
	use pocketmine\item\Item;
	use pocketmine\item\enchantment\Enchantment;
	use pocketmine\item\Armor;
	use pocketmine\inventory\PlayerInventory;

	use pocketmine\event\Listener;
	use pocketmine\event\player\PlayerJoinEvent;
	use pocketmine\event\player\PlayerDeathEvent;
	use pocketmine\event\player\PlayerDropItemEvent;
	use pocketmine\event\player\PlayerItemConsumeEvent;
	use pocketmine\event\entity\EntityDamageEvent;
	use pocketmine\event\entity\EntityDamageByEntityEvent;
	use pocketmine\event\block\BlockPlaceEvent;
	use pocketmine\event\block\BlockBreakEvent;

	use pocketmine\Player;
	use pocketmine\Server;

	class aQuestListener implements Listener{
		
		private $plugin;

		public function __construct(Main $plugin) {
			$this->plugin = $plugin;
		}

		public function onPlayerJoin(PlayerJoinEvent $event) {
			$name = strtolower($event->getPlayer()->getName());
			if(!isset($this->plugin->users[$name])) {
				$this->plugin->users[$name] = [
					'during' => false,
					'complete' => 0
				];
				$this->plugin->save();
			}
		}

		public function onBlockPlace(BlockPlaceEvent $event) {
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			if($this->plugin->users[$name]['during'] !== false)
				$this->checkQuest($player, $this->plugin->users[$name]['during'], 'blockplace', $event->getBlock()->getId());
		}

		public function onBlockBreak(BlockBreakEvent $event) {
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			if($this->plugin->users[$name]['during'] !== false)
				$this->checkQuest($player, $this->plugin->users[$name]['during'], 'blockbreak', $event->getBlock()->getId());
		}

		public function onPlayerDeath(PlayerDeathEvent $event) {
			$player = $event->getEntity();
			$name = strtolower($player->getName());
			if($this->plugin->users[$name]['during'] !== false)
				$this->checkQuest($player, $this->plugin->users[$name]['during'], 'playerkill');
			if($player->getLastDamageCause() instanceof EntityDamageByEntityEvent) {
				$killer = $player->getLastDamageCause()->getDamager();
				if($killer instanceof Player && $player instanceof Player) {
					$name = strtolower($killer->getName());
					if($player instanceof Player & $killer instanceof Player){
					if($this->plugin->users[$name]['during'] !== false)
						$this->checkQuest($killer, $this->plugin->users[$name]['during'], 'playerdeath');
				}
			}
		}
		}

		public function onPlayerDropItem(PlayerDropItemEvent $event) {
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			if($this->plugin->users[$name]['during'] !== false)
				$this->checkQuest($player, $this->plugin->users[$name]['during'], 'itemdrop');
		}

		public function onPlayerItemConsume(PlayerItemConsumeEvent $event) {
			$player = $event->getPlayer();
			$name = strtolower($player->getName());
			if($this->plugin->users[$name]['during'] !== false)
				$this->checkQuest($player, $this->plugin->users[$name]['during'], 'itemconsume');
		}

		public function checkQuest($player, $quest, $event, $bid = false) {
			$name = strtolower($player->getName());
			if($quest !== false) {
				if(strtolower($this->plugin->config['quests'][$quest['id']]['task']) == $event) {
					if($bid !== false) {
						if($this->plugin->config['quests'][$quest['id']]['id'] == $bid)
							$this->plugin->users[$name]['during']['count']++;
					}
					else {
						$this->plugin->users[$name]['during']['count']++;
					}
					if($this->plugin->config['quests'][$quest['id']]['num'] == $this->plugin->users[$name]['during']['count']) {
						$this->plugin->users[$name]['during'] = false;
						$this->plugin->users[$name]['complete']++;
						if(isset($this->plugin->config['quests'][$quest['id']]['award']))
							$this->giveAward($player, $this->plugin->config['quests'][$quest['id']]['award'], $quest['id']);
						else {
							$player->sendMessage(str_replace(['{quest}', '{count}'], [$this->plugin->config['quests'][$quest['id']]['name'], 1], $text));
						}
						$this->plugin->users[$name]['during'] = false;
					}
					$this->plugin->save();
				}
			}
		}

		public function giveAward($player, $award, $id) {
			if($award['id'] == 'badge') {
				if(!isset($award['count'])) {
					$this->getLogger()->warning("§lBạn chưa quy định số huy hiệu xa mạc! (count)");
					return true;
				}
				$this->plugin->badge->addBadge($player, $award['count']);
				$player->sendMessage(str_replace(['{quest}', '{award}', '{count}'], [$this->plugin->config['quests'][$id]['name'], isset($award['name']) ? $award['name'] : 'Монетки', $award['count']], $this->plugin->config['questEnd']));
				$player->addTitle("§aBạn đã hoàn thành nhiệm vụ seasonpass!","§e> Đã mở nhiệm vụ mới! <");
				return true;
			}

	}
}

?>