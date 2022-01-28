<?php
declare(strict_types=1);
namespace SizePLayer;
use pocketmine\{
  Server, Player
};
use pocketmine\command\{
  Command, CommandSender
};
use pocketmine\utils\TextFormat as TF;
use pocketmine\entity\Entity;
  
class SizePLayerCommand extends Command {
    
    /** var Plugin */
    private $plugin;
  
    public function __construct($plugin) {
        $this->plugin = $plugin;
        parent::__construct("size", "Change your player size!");
    }
    
    public function execute(CommandSender $player, string $label, array $args){
        if(!$player instanceof Player){
			$player->sendMessage(TF::RED."This command only works in-game");
			return;
		}
        if($player->hasPermission("size.command")) {
            if(isset($args[0])) {
                if(is_numeric($args[0])) {
                    if($args[0] > 5) {
                      $player->sendMessage(TF::RED. "§l§3●§e Kích thước này không được lớn hơn §a5");
                      return true;
                    }elseif($args[0] <= 0.3) {
                      $player->sendMessage(TF::RED. "§l§3●§e Kích thước này không thể nhỏ hơn §a0.3");
                      return true;
                    }
                    $this->plugin->size[$player->getName()] = $args[0];
                    $player->setScale((float)$args[0]);
                    $player->sendMessage("§l§3●§e Bạn đã thay đổi kích thước của bạn thành §a".TF::GOLD . $args[0]."!");
                }elseif($args[0] == "reset") {
                    if(!empty($this->plugin->size[$player->getName()])) {
                        unset($this->plugin->size[$player->getName()]);
                        $player->setScale(1);
                        $player->sendMessage("§l§3●§e Kích thước của bạn đã trở lại bình thường!");
                    }else{
                        $player->sendMessage("§l§3●§e Bạn đã đặt lại kích thước của bạn!");
                    }
                }else{
                    $player->sendMessage("§l§3●§e Ghi §b/size <Size> §eĐể Chỉnh Size §c|§e Ghi §b/size reset §eĐể Chỉnh Size Về Mặc Định");
                }
            } else {
              $player->sendMessage(TF::RED. "§l§3●§e Kích thước không phải là số hợp lệ!");
            }
            return true;
        }
        $player->sendMessage(TF::RED. "§l§6[§bSky§aBlock(Bin)§6]§e Bạn không được phép sử dụng lệnh size!");
    }
}
