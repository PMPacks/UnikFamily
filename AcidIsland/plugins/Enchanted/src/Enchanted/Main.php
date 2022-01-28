<?php

namespace Enchanted;

use pocketmine\{Server, Player};
use pocketmine\item\{Item, ItemFactory};
use pocketmine\level\Level;
use pocketmine\math\{VoxelRayTrace, Vector3};
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\level\Position;
use pocketmine\level\Explosion;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\{PlaySoundPacket, AddActorPacket};
use pocketmine\command\{Command, CommandSender};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\item\ItemBlock;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\particle\DestroyBlockParticle;
use jojoe77777\FormAPI;
use jojoe77777\FormAPI\SimpleForm;

class Main extends PluginBase implements Listener {
  
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    #đã fix lỗi không lấy được item và rút gọn nhất có thể rồi :)
    #và lời trân trọng gửi đến đạt là đụ má lười ít thôi lo mà làm server đi :>>>
    public function onCommand(CommandSender $sender, Command $cmd, string $label,array $args): bool{
        switch($cmd->getName()){
            case "item":
                if($sender instanceof Player){
                    $form = new SimpleForm(function (Player $player, $data){
                        $result = $data;
                        if($result === null){
                            return true;
                        }
                        switch($result){
                            case 0:
                                if(!$player->hasPermission("swords.god")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                        #Số Lượng Item Muốn Lấy  ↑
                                $sword = ItemFactory::get(Item::DIAMOND_SWORD);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§r§l§4Quỷ Kiếm");
$sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eQuỷ Kiếm (Lvl: 1)\n §r§cKĩ Năng\n§7[+] §r§ehút máu đối thủ\n§r§7[+]  §r§ehất tung đối thủ với độ cao thấp\n§b§l-=☯=-\n§l§7[===================================]\n§l§cĐộ bền §r§d■■§8□□□□□□□□\n§l§cSát thương §r§d■■§8□□□□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                            break;
                            case 1:
                                if(!$player->hasPermission("swords.teleport")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                                $sword = ItemFactory::get(Item::IRON_SWORD);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§r§l§eＫiếm Ảo Giác");
                                $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eKiếm Ảo Giác (Lvl: 1)\n§r§c Kĩ Năng:\n§r§7[+] §r§egây choáng và làm chậm đối thủ\n§r§7[+] §r§etự sửa vật phẩm khi di chuyển\n§l§7[=======================================]\n§l§cĐộ bền §r§d■■■§8□□□□□□□\n§l§cSát thương §r§d■■§8□□□□□□□\n§l§cGây cháy §r§d■■§8□□□□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                            break;
                            case 2:
                                if(!$player->hasPermission("swords.explode")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                                $sword = ItemFactory::get(Item::IRON_SWORD);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§lＫiếm §2Độc");
                                $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eKiếm Độc (Lvl: 1)\n§r§c Kĩ Năng\n§r§7[+] §r§eGây Độc lên đối thủ\n§l§7[=======================================]\n§l§cĐộ bền §r§d■§8□□□□□□□□□\n§l§cSát thương §r§d■§8□□□□□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                            break;
                            case 3:
                                if(!$player->hasPermission("swords.rabbit")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                                $sword = ItemFactory::get(Item::DIAMOND_SWORD);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§c§lＫiếm §6Rồng §5Thần");
                                $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eKiếm Rồng Thần (Lvl: 2)\n§r§c Kĩ Năng\n§r§7[+] §r§egây mù cho đối thủ trong thời gian ngắn\n§r§7[+] §r§eChoáng (Lvl: 2): §r§egây choáng kèm làm chậm đối thủ\n§l§7[=======================================]\n§l§cĐộ bền §r§d■■■■§8□□□□□□\n§l§cSát thương §r§d■■■■§8□□□□□\n§l§cGây cháy §r§d■■■§8□□□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                            break;
                            case 4:
                                if(!$player->hasPermission("swords.eme")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                                $sword = ItemFactory::get(Item::DIAMOND_SWORD);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§f【§6§lＫiếm §9Thủy Trụ§f】");
                                $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eKiếm Thuỷ Trụ (Lvl: 3)\n§r§c Nội Tại\n§r§b• §r§etăng thêm sát thương khi chạy\n§r§c Kĩ Năng\n§r§7[+] §r§egây choáng kèm làm chậm đối thủ\n§r§7[+] §r§etỉ lệ giảm các hiệu ứng có hại khi đang đánh nhau\n§r§7[+] §r§egây hiệu ứng khô héo vào đối thủ\n§l§7[=======================================]\n§l§cĐộ bền §r§d■■■■§8□□□□□□\n§l§cSát thương §r§d■■■■§8□□□□□\n§l§cGây cháy §r§d■■■§8□□□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                            break;
                            case 5:
                                                              if(!$player->hasPermission("swords.mavuong")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                                $sword = ItemFactory::get(Item::IRON_SWORD);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§f【§c§lＫiếm §5Ma §dVương§f】");
                                $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §e Kiếm Ma Vương (Lvl: 4)\n§r§c Nội Tại\n§r§b• §r§etăng thêm sát thương khi chạy\n§r§7[+] §r§egây mù đối thủ trong thời gian dài\n§r§7[+] §r§egăm sâu vết thương làm kẻ thù chảy máu\n§r§7[+] §r§eđâm sau kẻ thù gây sát thương gia tăng\n§r§7[+] §r§etự sửa vật phẩm khi di chuyển\n§l§7[=======================================]\n§l§cĐộ bền §r§d■■■■■■■■§8□□\n§l§cSát thương §r§d■■■■■■■■■§8□\n§l§cGây cháy §r§d■■■■■■§8□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                              break;
                              case 6:
                                      if(!$player->hasPermission("swords.1")) return;
                 $sword = ItemFactory::get(Item::BOW);
                  $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
           $sword->setCustomName("§lＣung §bHunter");
                   $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eCung Hunter (Lvl: 1)\n§r§c Nội Tại\n§r§b• §r§etăng 10% xuyên giáp kẻ thù\n§r§c Kĩ Năng\n§r§7[+] §r§ebắn vào đầu gây thêm ít sát thương\n§l§7[=======================================]\n§l§cĐộ bền §r§d■■§8□□□□□□□\n§l§cSát thương §r§d■■§8□□□□□□□\n§l§7[=======================================]"]);
                      $player->getInventory()->addItem($sword);
                             break;
                             case 7:
                                     if(!$player->hasPermission("swords.2")) return;
                                #có thể thay bằng ID Item để lấy được số lượng từ 1->64 tùy theo mình add
                        #sword = Item::get(0, 0, 0);
                                #ID Item   ↑
                        #ID Item Sau Giấu :   ↑
                                $sword = ItemFactory::get(Item::BOW);
                                $sword->setNamedTagEntry(new ListTag(Item::TAG_ENCH));
                                $sword->setCustomName("§lＣung §eZeus");
                                $sword->setLore(["§l§7[=======================================]\n§b§l-=☯=- §eZues (Lvl: 1)\n§r§c Kĩ Năng\n§r§7[+] §r§egây nôn mửa, làm chậm và yếu đi kẻ thù\n§r§7[+] §r§ebắn ra một vòng mũi tên hình tròn\n§l§7[=======================================]\n§l§cĐộ bền §r§d■■■§8□□□□□□\n§l§cSát thương §r§d■■■§8□□□□□□\n§l§7[=======================================]"]);
                                $player->getInventory()->addItem($sword);
                              break;
                        }
                    });                    
                    $form->setTitle("§l§6Item Trao Đổi");
$form->setContent("§8Lấy Các Item ở đây để làm vật phàm trao đổi\n§8NHÌN CON CỦ CẶC LO MÀ LẤY ĐỒ ĐI");
#Button Và Permission Để Lấy Item
if($sender->hasPermission("swords.god")) $form->addButton("§l§c...");
if($sender->hasPermission("swords.teleport")) $form->addButton("§l§b....");
if($sender->hasPermission("swords.explode")) $form->addButton("§l§6....");
if($sender->hasPermission("swords.rabbit")) $form->addButton("§l§e.....");
if($sender->hasPermission("swords.eme")) $form->addButton("§l§c.....");
if($sender->hasPermission("swords.mavuong")) $form->addButton("§l§c......");
if($sender->hasPermission("swords.1")) $form->addButton("§l§c......");
if($sender->hasPermission("swords.2")) $form->addButton("§l§c......");
 $form->sendToPlayer($sender);
                }
                            break;        
                        }
                        return true;
                    }
                }