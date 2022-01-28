<?php

	namespace AlexBrin;

	use pocketmine\plugin\PluginBase;
	use pocketmine\utils\Config;
	use pocketmine\command\Command;
	use pocketmine\command\CommandSender;

	use pocketmine\event\Listener;

	use pocketmine\Player;
	use pocketmine\Server;
    use jojoe77777\FormAPI\SimpleForm;
	class Main extends PluginBase {
		public $config, $users;

		public function onEnable() {
			$folder = $this->getDataFolder();
			if(!is_dir($folder))
			@mkdir($folder);
			$this->saveDefaultConfig();
			$this->config = (new Config($folder.'config.yml', Config::YAML))->getAll();
			$this->users = (new Config($folder.'users.yml', Config::YAML))->getAll();
			$this->getServer()->getPluginManager()->registerEvents(new aQuestListener($this), $this);
			$this->badge = $this->getServer()->getPluginManager()->getPlugin("Badge");
		}

		public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
			if($sender instanceof Player) {
				if(strtolower($command->getName()) == 'nhiemvu') {
					$name = strtolower($sender->getName());
					$num = $this->users[$name]['complete'];
					if($num == 0){
						$quest = $this->config['quests'][$num];
						$sender->sendMessage("§cKhông tìm thấy nhiệm vụ");
					}
					if($this->users[$name]['during'] !== false) {
						$num = $this->users[$name]['complete'];
						$quest = $this->config['quests'][$num];
						$this->MsgForm($sender, " §l§aTên nhiệm vụ: §c§l> §e{$quest['name']} §l§c<§r\n\n §l§aNhiệm vụ bạn cần làm: §f{$this->getTypeQuest($quest['task'])} §a{$quest['num']} §2{$quest['target']}");
						//$sender->sendMessage(str_replace(['\n', '{quest}', '{type}', '{target}', '{count}'], ["\n", $quest['name'], $this->getTypeQuest($quest['task']), $quest['target'], $quest['num']], $this->config['questHelp']));
						return false;
					}
					if($num >= count($this->config['quests'])) {
						$this->MsgForm($sender, " §l§aBạn đã hoàn thành tất cả nhiệm vụ seasonpass của máy chủ! Xin chúc mừng!");
						//$sender->sendMessage($this->config['questEnded']);
						return false;
					}
					$this->users[$name]['during'] = [
						'id' => $num,
						'count' => 0
					];
					$this->save();
					$this->MsgForm($sender, " §l§aTên nhiệm vụ: §c§l> §e{$quest['name']} §l§c<§r\n\n §l§aNhiệm vụ: §f{$this->getTypeQuest($quest['task'])} §a{$quest['num']} §2{$quest['target']}");
					//$sender->sendMessage(str_replace('{quest}', $quest['name'], $this->config['getQuest']));
					//$sender->sendMessage(str_replace(['\n', '{type}', '{target}', '{count}'], ["\n", $this->getTypeQuest($quest['task']), $quest['target'], $quest['num']], $this->config['getQuestInfo']));
				}
			} else $sender->sendMessage("§cKhông thể sử dụng lệnh");
			return false;
		}

		public function MsgForm(Player $player, $msg){
			$form = new SimpleForm(function (Player $player, ?int $data = null){
				$result = $data;
				if($data === null){
					return false;
				}
				switch ($result){
					case 0:
					break;
				}
			});
			$form->setTitle("§6§lNhiệm Vụ Sổ Xứ Mệnh");
			$form->setContent($msg);
			$form->addButton("Submit");
			$form->sendToPlayer($player);
			return $form;
		}

		/**
		 * @param string  $type
		 * @return string $type
		 */
		public function getTypeQuest($type) {
			switch(strtolower($type)) {

				case 'blockbreak':
						$type = 'Đập';
					break;

				case 'blockplace':
						$type = 'Đặt';
					break;

				case 'playerkill':
						$type = 'Giết';
					break;

				case 'playerdeath':
						$type = 'Chết';
					break;

				case 'itemconsume':
						$type = 'Tiêu thụ';
					break;

				case 'itemdrop':
						$type = 'Thả';
					break;

				default: 
						$type = '???';

			}
			return $type;
		}

		public function save() {
			$cfg = new Config($this->getDataFolder().'users.yml', Config::YAML);
			$cfg->setAll($this->users);
			$cfg->save();
		}

	}
