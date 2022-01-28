<?php
namespace TungstenVn\SeasonPass\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;


use TungstenVn\SeasonPass\SeasonPass;
use TungstenVn\SeasonPass\subCommands\addItem;
use TungstenVn\SeasonPass\menuHandle\menuHandle;
use TungstenVn\SeasonPass\subCommands\removeItem;
use TungstenVn\SeasonPass\subCommands\setItemInfo;

use TungstenVn\SeasonPass\libs\jojoe77777\FormAPI\SimpleForm;
class commands extends Command implements PluginIdentifiableCommand, Listener
{

    /*  Main Class (SeasonPass) */
    public $ssp;

    public function __construct(SeasonPass $ssp)
    {
        parent::__construct("ssp", "Sổ Xứ Mệnh", ("/ssp help"), []);
        $this->ssp = $ssp;
    }

    public function execute(CommandSender $sender, $commandLabel, array $args)
    {
        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                $a = new menuHandle($this, $sender);
                $this->ssp->getServer()->getPluginManager()->registerEvents($a, $this->ssp);
                return;
            }
            switch ($args[0]) {
                case 'a':
                case 'additem':
                    new addItem($this, $sender, $args);
                    break;
                case 'sl':
                case 'setlore':
                    new setItemInfo(1, $sender, $args);
                    break;
                case 'sn':
                case 'setname':
                    new setItemInfo(0, $sender, $args);
                    break;
                case 'r':
                case 'removeitem':
                    new removeItem($this, $sender, $args);
                    break;
                default:
                    $this->helpForm($sender,"");
                    break;
            }
        } else {
            $sender->sendMessage("Please run command in-game.");
        }
    }
    public function helpForm(Player $player,string $txt){
        $form = new SimpleForm(function(Player $player, int $data = null) {
            $result = $data;
            if ($result === null) {
                return;
            }
            switch ($result) {
                case 0:
                    $player->sendMessage("§a§l⊰ §fChúc bạn một ngày Tốt Lành §a⊱");
                    break;
                case 1:
                    $this->helpForm($player,"§a§l⊰ §fNếu bạn không hiểu thì hãy đọc lại nó §a⊱\n");
                    break;
                default:
                    break;
            }
        });
        $form->setTitle("§a§l⊰ §0Sổ Xứ Mệnh §a⊱");
        $form->setContent($txt."§a§l⊰ §eSử dụng lệnh /ssp để mở sổ xứ mệnh\n§a§l⊰ §eSử dụng Sổ Xứ Mệnh giống như cách bạn sử dụng Sổ Xứ Mệnh trên Liên Quân Mobile\n\n§a§l⊰ §eVới §fnormalPass,§e mọi người đều có thể yêu cầu một vật phẩm khi họ có đủ cấp độ, tuy nhiên, §cRoyalPass§c không dành cho tất cả mọi người, nó có nhiều vật phẩm có giá trị hơn 10.000 Points."
        );
        $form->addButton("Tôi đã hiểu", 0, "textures/items/light_block_7");
        $form->addButton("Tôn Chưa Hiểu", 0, "textures/items/light_block_0");
        $player->sendForm($form);
        return $form;
    }
    public function getPlugin(): Plugin
    {
        return $this->ssp;
    }
}
