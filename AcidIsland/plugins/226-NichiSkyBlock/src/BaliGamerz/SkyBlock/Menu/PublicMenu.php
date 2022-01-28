<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\Menu;


use BaliGamerz\SkyBlock\Entity\NpcClass;
use BaliGamerz\SkyBlock\FunctionLogic\ListFunction;
use BaliGamerz\SkyBlock\island\Island;
use BaliGamerz\SkyBlock\libraries\MadeForm\CustomForm;
use BaliGamerz\SkyBlock\libraries\MadeForm\ModalForm;
use BaliGamerz\SkyBlock\libraries\MadeForm\SimpleForm;
use BaliGamerz\SkyBlock\Main;
use BaliGamerz\SkyBlock\SkyBlockManager\SetupListener;
use BaliGamerz\SkyBlock\Utils;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\Server;

class PublicMenu
{
    public function getPlugin(): Main
    {
        return Main::getInstance();
    }

    public function sendMessageForm($sender, $message)
    {
        $sender->sendMessage("§f[§aSKYBLOCK§f] §f» §b" . $message);
    }


    public function listLogic(): ListFunction
    {
        return new ListFunction();
    }


    public function showRunningQuests(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0;
                    $this->playerFormClicked($player);
                    break;
            }
        });
        $playerData = $this->getPlugin()->hasPlayerQuest($player);
        if ($playerData !== null) {
            $progressTotal = ((int)$playerData['objectiveData']['progress'] / (int)$playerData['objectiveData']['item']['amount']) * 100;
            $arrayContent = [
                "§6=====================§r",
                "§fTên nhiệm vụ: §a" . $playerData['objectiveData']['name'],
                "       ",
                "§fID Nhiệm Vụ: §d" . $playerData['objectiveData']['questID'],
                "§fLoại nhiệm vụ: §c" . $playerData['objectiveData']['type'],
                "       ",
                "§fTiến Độ: §6" . $this->formatInt($playerData['objectiveData']['progress']) . "§7/§a" . $this->formatInt($playerData['objectiveData']['item']['amount']),
                "§7[§r" . Utils::getProgress($progressTotal) . "§7]",
                "       ",
                "§fItem: ",
                "  §fId: §a" . $playerData['objectiveData']['item']['id'],
                "  §fMeta: §a" . $playerData['objectiveData']['item']['meta'],
                "§6====================="
            ];
        } else {
            $arrayContent = [
                "§6=====================§r",
                "§fTên nhiệm vụ: §aKhông có nhiệm vụ",
                "        ",
                "§fID Nhiệm Vụ: §dKhông có nhiệm vụ",
                "§fLoại nhiệm vụ: §cKhông có nhiệm vụ",
                "       ",
                "§fTiến Độ: §6Không có nhiệm vụ",
                "§7[§7]",
                "       ",
                "§fItem: ",
                "  §fId: §aKhông có nhiệm vụ",
                "  §fMeta: §aKhông có nhiệm vụ",
                "§6====================="
            ];
        }
        $form->setTitle("§a» §8Khởi Động Nhiệm Vụ §a«");
        $form->setContent(implode("\n", $arrayContent));
        $form->addButton("§cBack\n§7Click to use");
        $form->sendToPlayer($player);
    }

    public function depositIslandMoney(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) $this->moneyMenu($player);
            if (!isset($data[1])) {
                $this->sendMessageForm($player, "§c Vui lòng nhập số nguyên");
                return;
            }
            $mentahanMoney = (int)$data[1];
            $moneyInteger = (int)str_replace('-', '', $mentahanMoney);
            if (!is_int($moneyInteger)) {
                $this->sendMessageForm($player, "§cPlease input integer not string");
                return;
            }
            $island = $this->getPlugin()->getIslandManager()->getIslandByPlayer($player);
            if ($island !== null) {
                if ($island->reduceMoney($moneyInteger)) {
                    $this->getPlugin()->economy->addMoney($player,$moneyInteger);
                    $this->sendMessageForm($player, "§aSend " . $this->formatInt($moneyInteger) . " $ Đã Nhận");
                    Utils::addSound($player, 2, 1, 'random.levelup');
                } else {
                    $this->sendMessageForm($player, "§cHết Tiền " . $this->formatInt($moneyInteger));
                }
            }
        });
        $form->setTitle("§a» §8Gửi tiền §a«");
        $form->addLabel("§b» §3Island Money: §7[§a" . $this->formatInt($this->getPlugin()->getIslandMoney($player)) . "§7/§e" . $this->formatInt($this->getPlugin()->getIslandMaxMoney($player)) . "§7]\n§b» §3Số Tiền Của Bạn: §a" . $this->formatInt($this->getPlugin()->economy->myMoney($player)) . "\n");
        $form->addInput("Nhập số tiền", "Nhập Vào Đây");
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     */
    public function buyIsland(Player $player)
    {
        $dataIsland = $this->getPlugin()->dataThemas['list'];
        $rows = [];
        foreach ($dataIsland as $slot => $value) {
            if ($value['payment'] !== false) {
                $rows[$slot] = $value;
            }
        }
        $form = new SimpleForm(function (Player $player, $data = null) use ($rows) {
            if ($data === null) return;
            if ($data === 0) {
                $this->sendMessageForm($player, "Thanks for see");
            } else {
                $economy = $this->getPlugin()->economy;
                $array = array_keys($rows);
                $button = $array[$data - 1];
                $dataTheme = $rows[$button];
                if ($economy->myMoney($player) >= $dataTheme['payment']['price']) {
                    $this->getPlugin()->economy->reduceMoney($player, (int)$dataTheme['payment']['price']);
                    $item = Item::get((int)$dataTheme['item']['id'], (int)$dataTheme['item']['meta']);
                    $tag = $item->hasCompoundTag() ? $item->getNamedTag() : new CompoundTag();
                    $tag->setFloat($dataTheme['payment']['id'], 1.0);
                    $item->setNamedTag($tag)->setCustomName($dataTheme['item']['name'] . " Island")->setLore($dataTheme["item"]["lore"]);
                    $player->getInventory()->addItem($item);
                    Utils::addSound($player, 400, 1, "random.levelup");
                } else {
                    $this->sendMessageForm($player, "You don't have enough money");
                }
            }
        });
        $economy = $this->getPlugin()->economy;
        $form->setTitle("§a» §8Mua một hòn đảo §a«");
        $form->addButton("§cExit\n§7Click to use");
        foreach ($rows as $slot => $value) {
            if ($economy->myMoney($player) >= $value['payment']['price']) {
                $form->addButton("§a» §3" . $value['name'] . "\n§aClick to buy", $value['button-type'], $value['button-img']);
            } else {
                $form->addButton("§a» §3" . $value['name'] . "\n§cGiá bán: " . $this->formatInt($value['payment']['price']), $value['button-type'], $value['button-img']);
            }
        }
        $form->sendToPlayer($player);
    }


    /**
     * @param Player $player
     * @param $type
     * @return Item|null
     */
    public function hasPlayerVoucher(Player $player, $type): ?Item
    {
        foreach ($player->getInventory()->getContents() as $slot => $items) {
            if ($items->getNamedTag()->hasTag($type)) {
                return $items;
            }
        }
        return null;
    }

    /**
     * @param Player $player
     * @param $type
     * @return array|null
     */
    public function getArrayVoucher(Player $player, $type): ?array
    {
        foreach ($player->getInventory()->getContents() as $slot => $items) {
            if ($items->getNamedTag()->hasTag($type)) {
                return ['item' => $items, 'index' => $slot];
            }
        }
        return null;
    }


    /**
     * @param Player $player
     */
    public function buyMana(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) return;
            if (!isset($data[1])) {
                $this->sendMessageForm($player, "§cPlease input integer");
                return;
            }
            $mentahanMoney = (int)$data[1];
            $moneyInteger = (int)str_replace('-', '', $mentahanMoney);
            if (!is_int($moneyInteger)) {
                $this->sendMessageForm($player, "§cPlease input integer not string");
                return;
            }

            $price = $this->getPlugin()->config['user']['mana_price'];
            $money = $this->getPlugin()->economy;
            $result = $price * $data[1];
            if ($money->myMoney($player) >= $result) {
                if ($this->getPlugin()->addPlayerMana($player, $moneyInteger)) {
                    $this->sendMessageForm($player, "§aMua thành công §b" . $moneyInteger . " Mana");
                    $this->getPlugin()->economy->reduceMoney($player, $result);
                    Utils::addSound($player, 2, 6, 'note.bit');
                } else {
                    $this->sendMessageForm($player, "§cMana của bạn đã đầy.");
                }
            } else {
                $this->sendMessageForm($player, "§cYou don't have money");
            }
        });
        $form->setTitle("§a» §8Mua Mana §a«");
        $form->addLabel("§b» §3Số Tiền Của Bạn: §a" . $this->formatInt($this->getPlugin()->economy->myMoney($player)) . "\n    \n§bGiá Bán\n§b1 Mana = §e" . $this->formatInt($this->getPlugin()->config['user']['mana_price']));
        $form->addInput("Nhập Số lượng", "Nhập Vào Đây");
        $form->sendToPlayer($player);
    }

    public function addIslandMoney(Player $player)
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) $this->moneyMenu($player);
            if (!isset($data[1])) {
                $this->sendMessageForm($player, "§cPlease input integer");
                return;
            }
            $mentahanMoney = (int)$data[1];
            $moneyInteger = (int)str_replace('-', '', $mentahanMoney);
            if (!is_int($moneyInteger)) {
                $this->sendMessageForm($player, "§cPlease input integer not string");
                return;
            }
            $moneyInteger = (int)$moneyInteger;
            $island = $this->getPlugin()->getIslandManager()->getIslandByPlayer($player);
            if ($island !== null) {
                if ($island->addMoney($moneyInteger)) {
                    if ($this->getPlugin()->economy->myMoney($player) >= $moneyInteger) {
                        $this->getPlugin()->economy->reduceMoney($player, $moneyInteger);
                        $this->sendMessageForm($player, "§aTiết kiệm thành công tiền ở đảo");
                        Utils::addSound($player, 2, 1, 'random.levelup');
                    } else {
                        $this->sendMessageForm($player, "§cYou don't have money");
                    }
                } else {
                    $this->sendMessageForm($player, "§cKhả năng đảo tiền đã đầy. Vui lòng cập nhật dung lượng đảo");
                }
            }
        });
        $form->setTitle("§a» §8Thêm tiền §a«");
        $form->addLabel("§b» §3Island Money: §7[§a" . $this->formatInt($this->getPlugin()->getIslandMoney($player)) . "§7/§e" . $this->formatInt($this->getPlugin()->getIslandMaxMoney($player)) . "§7]\n§b» §3Số Tiền Của Bạn: §a" . $this->formatInt($this->getPlugin()->economy->myMoney($player)) . "\n");
        $form->addInput("Nhập Số Tiền Muốn Gửi", "Nhập Vào Đây");
        $form->sendToPlayer($player);
    }

    /**
     * @param Player $player
     */
    public function moneyMenu(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $this->playerFormClicked($player);
                    break;
                case 1:
                    $this->addIslandMoney($player);
                    break;
                case 2:
                    $this->depositIslandMoney($player);
                    break;
                case 3:
                    $this->updateTier($player);
                    break;
            }
        });
        $form->setTitle("§a» §8Island Bank §a«");
        $form->setContent("§b» §3Island Money: §7[§a" . $this->formatInt($this->getPlugin()->getIslandMoney($player)) . "§7/§e" . $this->formatInt($this->getPlugin()->getIslandMaxMoney($player)) . "§7]\n§b» §3Island Tier: §d" . $this->getPlugin()->getIslandMoneyTier($player));
        $form->addButton("§cBack\n§7Click to use");
        $form->addButton("§b» §2Thêm tiền vào đảo\n§7Click to use");
        $form->addButton("§b» §2Lấy tiền của đảo\n§7Click to use");
        $form->addButton("§b» §2Nâng Cấp Kho Tiền\n§7Click to update");
        $form->sendToPlayer($player);
    }

    public function updateTier(Player $player)
    {
        $form = new ModalForm(function (Player $player, $data = null) {
            if ($data === null) $this->moneyMenu($player);
            switch ($data) {
                case 1:
                    $island = $this->getPlugin()->getIslandManager()->getIslandByPlayer($player);
                    if ($island instanceof Island) {
                        $money = $this->getPlugin()->economy->myMoney($player);
                        if ($money >= $island->getPriceTier()) {
                            if ($island->updateMoneyTier()) {
                                Utils::addSound($player, 2, 1, "random.levelup");
                                $this->sendMessageForm($player, "§aCập nhật Kho Đựng Tiền thành công");
                            } else {
                                $this->sendMessageForm($player, "§cHòn đảo của bạn đã được cấp tối đa");
                            }
                        } else {
                            $this->sendMessageForm($player, "§cYou don't have enought a money");
                        }
                    }
                    break;
            }
        });
        $tierArray = $this->getPlugin()->getNextIslandMoneyTier($player);
        $form->setTitle("§a» §8Update Tier §a«");
        $form->setContent("§eBạn sẽ cập nhật\n§eKho Tiền Của Đảo  §d" . $tierArray['next-int'] . "\n§evới việc bổ sung một lượng: §a" . $tierArray['next-money'] . "\n§e với giá cả: §f" . $tierArray['price'] . "\n\n§eBấm OK để tiếp tục.\n§eNhấp vào Cancel để hủy bỏ");
        $form->setButton1("§b» §2OK");
        $form->setButton2("§b» §cCancel");
        $form->sendToPlayer($player);
    }


    /**
     * @param $n
     * @param int $precision
     * @return string
     */
    public function formatInt($n, $precision = 1): string
    {
        $n = is_numeric($n) ? $n : 0;
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = 'C';
        } else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        } else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        } else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }
        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }

        return $n_format . $suffix;
    }


    /**
     * @param Player $player
     * @param array $result
     */
    public function selectedFreeOrPaid(Player $player, array $result)
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($result) {
            switch ($data) {
                case 0:
                    $this->paidConfigure($player, $result);
                    break;
                case 1:
                    $name = $result['name'];
                    $dirname = $result['dirname'];
                    $button_img = $result['button-img'];
                    $button_type = $result['button-type'];
                    $this->getPlugin()->queue[strtolower($player->getName())] = true;
                    $resultData = [
                        'name' => $name,
                        'index' => 1,
                        "dirname" => $dirname,
                        "spawn" => null,
                        "button-type" => $button_type,
                        "button-img" => $button_img,
                        "npcPosition" => null,
                        "signPosition" => null,
                        "item" => [],
                        "payment" => false];
                    $this->getPlugin()->getServer()->getPluginManager()->registerEvents(new SetupListener($this->getPlugin(), $player, $resultData), $this->getPlugin());
                    $this->sendMessageForm($player, "Create themes with name " . ucfirst($name) . " Successfully\nPlease break block to set island spawn");
                    break;
            }
        });
        $form->setTitle("§a» §8Chọn phương thức thanh toán §a«");
        $form->addButton("§b» §3Trả tiền?");
        $form->addButton("§b» §3Miễn phí?");
        $form->sendToPlayer($player);
    }


    public function paidConfigure(Player $player, array $result, $content = "§ePlease configure of\npayment manager")
    {
        $form = new CustomForm(function (Player $player, $data = null) use ($result) {
            if ($data === null) return;
            if ($data[1] === null) {
                $this->paidConfigure($player, $result, "§c Vui lòng định cấu hình giá");
                return;
            }
            if ($data[3] === null) {
                $this->paidConfigure($player, $result, "§c Vui lòng định cấu hình mục dữ liệu");
                return;
            }
            $explode = explode(":", $data[3]);
            $arrayToString = implode(":", array_slice($explode, 3));
            $explodeLore = explode(":", $arrayToString);
            if ($explode[0] === null) {
                $this->paidConfigure($player, $result, "§c Vui lòng định cấu hình Mục dữ liệu");
                return;
            }
            if ($explode[1] === null) {
                $this->paidConfigure($player, $result, "§c Vui lòng định cấu hình mục dữ liệu");
                return;
            }
            if ($explode[2] === null) {
                $this->paidConfigure($player, $result, "§c Vui lòng định cấu hình mục dữ liệu");
                return;
            }
            $item = [
                "name" => $explode[0],
                "id" => $explode[1],
                "meta" => $explode[2],
                "lore" => $explodeLore
            ];
            $name = $result['name'];
            $dirname = $result['dirname'];
            $button_img = $result['button-img'];
            $button_type = $result['button-type'];
            $payment = [
                "id" => $name,
                "price" => $data[1]
            ];
            $this->getPlugin()->queue[strtolower($player->getName())] = true;
                $resultData = [
                'name' => $name,
                'index' => 1,
                "dirname" => $dirname,
                "spawn" => null,
                "button-type" => $button_type,
                "button-img" => $button_img,
                "npcPosition" => null,
                "signPosition" => null,
                "item" => $item,
                "payment" => $payment];
            $this->getPlugin()->getServer()->getPluginManager()->registerEvents(new SetupListener($this->getPlugin(), $player, $resultData), $this->getPlugin());
            $this->sendMessageForm($player, "§eTạo Đảo Với Tên " . ucfirst($name) . " Thành công\n Vui lòng phá vỡ khối để thiết lập đảo");
        });
        $form->setTitle("§a» §8Trả Phí §a«");
        $form->addLabel($content);
        $form->addInput("Giá", "Viết ở đây");
        $form->addLabel("\nItem data\nExample:\nCustomName:ID:Meta:Lore1:Lore2:Lore3");
        $form->addInput("Item Data", "write Item data", "BasicIsland:339:0:Tôi có bạn thích: trong skyblock này:      :Sử dụng mục này nếu bạn tạo một hòn đảo");
        $form->sendToPlayer($player);
    }


    public function createThemesForm(Player $player, $content = '')
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) return;
            if ($data[1] === null) {
                $this->themesManager($player, "§cPlease valid configure");
                return;
            }
            if ($data[2] === null) {
                $this->themesManager($player, "§cPlease valid configure");
                return;
            }
            if (isset($this->getPlugin()->queue[strtolower($player->getName())])) {
                $this->themesManager($player, "§cBạn đã tạo chế độ thiết lập. Vui lòng hủy.\nĐể tạo các chủ đề khác");
                return;
            }
            $name = $data[1];
            if (isset($this->getPlugin()->dataThemas['list'][$data[2]])) {
                $this->themesManager($player, "§cWorld name already register this server");
                return;
            }
            $player->getLevel()->save(true);
            $this->selectedFreeOrPaid($player, ["name" => $name, "dirname" => $data[2], "button-type" => $data[3] ?? 0, "button-img" => $data[4] ?? "textures/ui/icon_trailer"]);
        });
        $form->setTitle("§a» §8Chủ Đề §a«");
        $form->addLabel($content);
        $form->addInput("Tên Chủ Đề", "Viết ở đây");
        $form->addInput("Viết tên thế giới", "Viết ở đây", $player->getLevel()->getFolderName());
        $form->addInput("Loại Buttons", "0 or 1", "0");
        $form->addInput("Ảnh Buttons", "Viết ở đây", "textures/ui/icon_trailer");
        $form->sendToPlayer($player);
    }

    public function themesManager(Player $player, $content = '')
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    if (!isset($this->getPlugin()->queue[strtolower($player->getName())])) {
                        $this->themesManager($player, "§cYou can't this setup mode ");
                        return;
                    }
                    unset($this->getPlugin()->queue[strtolower($player->getName())]);
                    $this->themesManager($player, "§aYou success cancel setup mode");
                    break;
                case 1:
                    $this->createThemesForm($player);
                    break;
                case 2:
                    $this->settingForm($player);
                    break;
            }
        });
        $form->setTitle("§a» §8Trình quản lý chủ đề §a«");
        $form->setContent($content);
        $form->addButton("§dHủy hàng đợi\n§7Click to cancel");
        $form->addButton("§dThêm Chủ Đề\n§7Click to use");
        $form->addButton("§cBack\n§7Click to use");
        $form->sendToPlayer($player);
    }

    public function successCreateTheme(Player $player, $content, $theme)
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($theme){
            if ($data === null) return;
            switch ($data) {
                case 0:
                    if (!isset($this->getPlugin()->queue[strtolower($player->getName())])) {
                        $this->settingForm($player, "You can't this setup mode ");
                        return;
                    }
                    $dir = $theme['dirname'];
                    Utils::copy(Server::getInstance()->getDataPath() . "worlds/" . $dir, $this->getPlugin()->getDataFolder() . $dir);
                    $this->settingForm($player, "Success setup themes");
                    unset($this->getPlugin()->queue[strtolower($player->getName())]);
                    break;
                case 1:
                    if (!isset($this->getPlugin()->queue[strtolower($player->getName())])) {
                        $this->settingForm($player, "You can't this setup mode ");
                        return;
                    }
                    unset($this->getPlugin()->queue[strtolower($player->getName())]);
                    $this->settingForm($player, "You success cancel setup mode");
                    break;
            }
        });
        $form->setTitle("§c» §8Cài Đặt Giao Diện §c«");
        $form->setContent($content);
        $form->addButton("§dXong\n§7Click to use");
        $form->addButton("§dCancel\n§7Click to use");
        $form->sendToPlayer($player);
    }


    public function createIslandForm($player, $content = "§aCreated a new island\n")
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $island_name = str_replace($this->onProtect(), $this->onReplace(), $data[1]);
            if (file_exists($this->getPlugin()->getDataFolder() . "islands/{$island_name}.json")) {
                $this->onNameAlready($player);
                return;
            }
            if (is_dir($this->getPlugin()->getServer()->getDataPath() . "worlds/" . $island_name)) {
                $this->onNameAlready($player);
                return;
            }
             if($data[1] === null){
                $this->onNameAlready($player, 'Not use int name');
                return;
            }
            $this->selectedThemasIsland($player, $island_name);
        });
        $form->setTitle("§a» §8Tạo Island §a«");
        $form->addLabel($content);
        $form->addInput("\n§aVui lòng nhập tên\n§ahòn đảo của bạn trong cột bên dưới\n", "Type String");
        $form->sendToPlayer($player);
    }

    public function onNameAlready($player, $content = 'Island name already in use')
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $island_name = str_replace($this->onProtect(), $this->onReplace(), $data[1]);
            if (file_exists($this->getPlugin()->getDataFolder() . "islands/{$island_name}.json")) {
                $this->onNameAlready($player);
                return;
            }
            if (is_dir($this->getPlugin()->getServer()->getDataPath() . "worlds/" . $island_name)) {
                $this->onNameAlready($player);
                return;
            }
             if($data[1] === null){
                $this->onNameAlready($player, 'Not use int name');
                return;
            }
            $this->selectedThemasIsland($player, $island_name);
        });
        $form->setTitle("§a» §8Tạo Island  §a«");
        $form->addLabel("§eWarning!. §c{$content}\n§c Hãy nghĩ 1 tên bất kì");
        $form->addInput("\n§a Vui lòng nhập tên của \nđảo của bạn vào cột bên dưới\n", "Type String");
        $form->sendToPlayer($player);
    }

    public function settingForm(Player $player, $content = '')
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $this->themesManager($player);
                    break;
                case 1:
                    $this->addNpc($player);
                    break;
                case 2:
                    $this->getPlugin()->lobbyData['lobbyPosition'] = [
                        'vector' => $player->getX() . ":" . ($player->getY() + 2) . ":" . $player->getZ(),
                        'level' => $player->getLevel()->getFolderName()];
                    $this->getPlugin()->setLobby();
                    $this->sendMessageForm($player, "§aĐặt sảnh thành công");
                    break;
            }
        });
        $form->setTitle("§a» §8Cài Đặt §a«");
        $form->setContent($content);
        $form->addButton("§3Chủ Đề Đảo\n§7Click to select!");
        $form->addButton("§3Quản lý Npc\n§7Click to select!");
        $form->addButton("§3Set Lobby Server\n§7Click to select!");
        $form->addButton("§cExit\n§7Click to select!");
        $form->sendToPlayer($player);
    }

    public function playerFormClicked(Player $player, $content = '')
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $lobby = $this->getPlugin()->lobbyServer;
                    if ($lobby !== null) {
                        if (!$this->getPlugin()->getServer()->isLevelLoaded($lobby->getLevel()->getFolderName())) {
                            $this->getPlugin()->getServer()->loadLevel($lobby->getLevel()->getFolderName());
                        }
                        $player->teleport($lobby);
                    } else {
                        $this->sendMessageForm($player, "§cLobby not register");
                    }
                    break;
                case 1:
                    $this->showRunningQuests($player);
                    break;
                case 2:
                    $this->onMenu($player);
                    break;
                case 3:
                    $this->showAchievement($player);
                    break;
                case 4:
                    $this->islandStats($player);
                    break;
                case 5:
                    $this->moneyMenu($player);
                    break;
                case 6:
                    (new ShopLogic())->onOpen($player);
                    break;
                case 7:
                    $this->playerFormClicked($player);
                    break;
            }
        });
        $form->setTitle("§a» §8Menu Island §6" . $player->getName() . " §a«");
        $form->setContent($content);
        $form->addButton("§3Đi đến sảnh\n§7Click to select!");
        $form->addButton("§3Nhiệm vụ\n§7Click to select!");
        $form->addButton("§3Island Menu\n§7Click to select!");
        $form->addButton("§3Thành tích\n§7Click to select!");
        $form->addButton("§3Island Stats\n§7Click to select!");
        $form->addButton("§3Island Money\n§7Click to select!");
        $form->addButton("§3Shops\n§7Click to select!");
        $form->addButton("§bBack\n§7Click to select!");
        $form->addButton("§cExit\n§7Click to select!");
        $form->sendToPlayer($player);
    }


    public function islandStats(Player $player)
    {
        $main = $this->getPlugin();
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
        });
        $form->setTitle("§a» §8Thống kê Đảo §a«");
        $island = $this->getPlugin()->getIslandManager()->getIslandByPlayer($player);
        $content = '';
        $format = [];
        if ($island !== null) {
            $members = $island->getAllMembers();
            foreach ($members as $member) {
                $content .= "§d- §a" . $member . "§r\n";
            }
            $format = [
                "§fLevel: §7" . $island->getLevel() . "",
                "        ",
                "§fMine: §a" . $island->getMine(),
                "§fTiến Độ: §b" . $main->getIntProgress($player) . "§7/§a" . $main->getProgressSize($player),
                "§7[" . $main->getProgress($player) . "§7]",
                "         ",
                "§fKho Tiền: §6" . $this->formatInt($island->money['money']),
                "§fKích thước: §c" . $main->getIslandSize($player),
                "         ",
                "§fMembers: §e" . count($island->getPlayersOnline()) . "§7/§a" . count($island->getAllMembers()),
                "§fDanh sách thành viên" => $content
            ];
        }
        $form->setContent(implode("\n", $format));
        $form->addButton("§cExit\n§7Click to select!");
        $form->sendToPlayer($player);
    }

    public function showAchievement(Player $player)
    {
        $from = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0;
                    $this->playerFormClicked($player);
                    break;
            }
        });
        $arrayQuests = $this->getPlugin()->quests['quests'];
        $dataPlayer = $this->getPlugin()->playerDataPath[strtolower($player->getName())]['objectives']['success'];
        $content = "\n";
        foreach ($arrayQuests as $slot => $value) {
            if (in_array($value['questID'], $dataPlayer)) {
                $content .= "§e" . $value['name'] . "\n §b>> §aHoàn thành §b<<\n\n";
            } else {
                $content .= "§e" . $value['name'] . "\n §b>> §cKhông hoàn thành §b<<\n\n";
            }
        }
        $from->setTitle("§a» §8Thành tích §a«");
        $from->setContent($content);
        $from->addButton("§b» §eOK");
        $from->sendToPlayer($player);
    }


    public function addNpc(Player $player, $content = '')
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $this->listLogic()->createLeaderBoard($player, "&6&lĐảo của bạn|Player Profile\n      \n&fLevel: &7{level}\n&fMine: &a{mine}\n&f Tiến Độ: &b{progress_int}&7/&a{progress_size}\n&7[{progress}&7]\n&fIsland Name: &a{sky_name}\n&fKho Tiền: &a{balance}\n&fHoàn thành: &a{task}\n&fKích Thước: &c{size}\n       \n&l&eCLICK FOR STATS", false);
                    break;
                case 1:
                    $this->listLogic()->createLeaderBoard($player, "{mine_top}", true, 1.5);
                    break;
                case 2:
                    $this->listLogic()->createLeaderBoard($player, "{size_top}", true, 1.5);
                    break;
                case 3:
                    $this->listLogic()->createLeaderBoard($player, "{level_top}", true, 1.5);
                    break;
                case 4:
                    $this->listLogic()->createLeaderBoard($player, "{money_top}", true, 1.5);
                    break;
                case 5:
                    $entity = $this->getNearFloating($player);
                    if ($entity !== null) {
                        $entity->close();
                        $this->sendMessageForm($player, "§aSuccess removing npc");
                    } else {
                        $this->sendMessageForm($player, "§cNot found npc");
                    }
                    break;
                case 6:
                    $this->settingForm($player);
                    break;
            }
        });
        $form->setTitle("§b» §8Spawn NPC §b«");
        $form->setContent($content);
        $form->addButton("§dNpc Thống kê\n§7Click to spawn!");
        $form->addButton("§dTop Mine\n§7Click to spawn!");
        $form->addButton("§dTop Size\n§7Click to spawn!");
        $form->addButton("§dTop Level\n§7Click to spawn!");
        $form->addButton("§dTop Money\n§7Click to spawn!");
        $form->addButton("§dXóa Npc \n§7Click to remove!");
        $form->addButton("§cBack\n§7Click to select!");
        $form->sendToPlayer($player);
    }


    public function getNearFloating(Player $player): ?NpcClass
    {
        $level = $player->getLevel();
        foreach ($level->getEntities() as $entity) {
            if ($entity instanceof NpcClass) {
                if ($player->distance($entity) <= 2 && $entity->distance($player) > 0) {
                    return $entity;
                }
            }
        }
        return null;
    }

    /**
     * @param Player $sender
     * @param $island
     */
    public function selectedThemasIsland(Player $sender, $island)
    {
        if (count($this->getPlugin()->dataThemas['list']) < 1) {
            $this->sendMessageForm($sender, "This server don't have island");
            return;
        }
        $dataIsland = $this->getPlugin()->dataThemas['list'];
        $rows = [];
        foreach ($dataIsland as $name => $valueOfIsland) {
            if ($valueOfIsland['payment'] === false) {
                $rows[$name] = $valueOfIsland;
            } else {
                if ($this->hasPlayerVoucher($sender, $valueOfIsland['payment']['id']) !== null) {
                    $rows[$name] = $valueOfIsland;
                }
            }
        }
        if (count($rows) < 0) {
            $this->sendMessageForm($sender, "§cBạn không có chủ đề về đảo. Vui lòng mua. Sử dụng /buyisland");
            return;
        }
        $form = new SimpleForm(function (Player $sender, $data = null) use ($island, $rows) {
            if ($data === null) {
                $this->createIslandForm($sender, "§c Vui lòng chọn chủ đề đảo\n");
                return;
            }
            if ($data === 0) {
                $this->createIslandForm($sender, "§c Vui lòng chọn chủ đề đảo\n");
            } else {
                $array = array_keys($rows);
                $button = $array[$data - 1];
                $islandName = $island ?? $sender->getName();
                $function = new ListFunction();
                $theme = $rows[$button];
                if ($function->createFunction($sender, $islandName, $rows[$button])) {
                    if ($theme['payment'] !== false) {
                        $item = $this->getArrayVoucher($sender, $theme['payment']['id']);
                        $item['item']->setCount($item['item']->getCount() - 1);
                        $sender->getInventory()->setItem($item['index'], $item['item']);
                    }
                }
            }
        });
        $form->setTitle("§a» §8Selected Theme §a«");
        $form->addButton("§cBack\n§7Click to select!");
        foreach ($rows as $slot => $value) {
            $form->addButton("§b» §3" . $value['name'] . "\n§8Click To Select", $value['button-type'], $value['button-img']);
        }
        $form->sendToPlayer($sender);
    }

    public function onMenu($sender, $content = '')
    {
        $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
        if (empty($config["island"])) {
            $this->createIslandForm($sender);
        } else {
            $form = new SimpleForm(function (Player $sender, $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        $this->listLogic()->joinFunction($sender);
                        break;
                    case 1:
                        $this->listLogic()->homeFunction($sender);
                        break;
                    case 2:
                        $this->manageIsland($sender);
                        break;
                    case 3:
                        $this->managePlayer($sender);
                        break;
                    case 4:
                        $this->visitIsland($sender);
                        break;
                }
            });
            $form->setTitle("§a» §8SkyBlock Menu §a«");
            $form->setContent($content);
            $form->addButton("Về Đảo\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
            $form->addButton("Home\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
            $form->addButton("Quản lý đảo\n§d§l»§r §7Tap to select!", 0, "textures/ui/icon_recipe_item");
            $form->addButton("Quản lý thành viên\n§d§l»§r §7Tap to select!", 0, "textures/ui/icon_multiplayer");
            $form->addButton("Ghé thăm đảo\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
            $form->addButton("§cExit", 0, "textures/blocks/barrier");
            $form->sendToPlayer($sender);
        }
    }

    public function manageIsland($sender, $content = '')
    {
        $form = new SimpleForm(function (Player $sender, $data) {
            if ($data === null) return;
            switch ($data) {
                case 0:
                    $this->listLogic()->lockFunction($sender);
                    break;
                case 1:
                    $this->listLogic()->setHomeFunction($sender);
                    break;
                case 2:
                    $this->listLogic()->resetFunction($sender);
                    break;
                case 3:
                    $this->listLogic()->disbandFunction($sender);
                    break;
                case 4:
                    $this->visitIsland($sender);
                    break;
                case 5:
                    $this->onMenu($sender);
                    break;
                case 6:
                    break;
            }
        });
        $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
        $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);

        $form->setTitle("§a» §8Island Manager §a«");
        $form->setContent($content);
        if ($island instanceof Island) {
            if ($island->isLocked()) {
                $form->addButton("Mở khóa đảo\n§d§l»§r §7Tap to select!", 0, "textures/ui/icon_unlocked");
            } else {
                $form->addButton("§8Khoá Đảo\n§d§l»§r §7Tap to select!", 0, "textures/ui/icon_lock");
            }

        }
        $form->addButton("Sethome\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Reset\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Xóa đảo\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Ghé thăm đảo khác\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Back\n§d§l»§r §7Tap to select!", 0, "textures/ui/book_arrowleft_hover");
        $form->addButton("§cExit", 0, "textures/blocks/barrier");
        $form->sendToPlayer($sender);
    }


    /**
     * @param $sender
     * @param string $content
     */
    public function managePlayer($sender, $content = '')
    {
        $form = new SimpleForm(function (Player $sender, $data) {
            if ($data === null) return;

            switch ($data) {
                case 0:
                    $this->kickTarget($sender);
                    break;
                case 1:
                    $this->inviteTarget($sender);
                    break;
                case 2:
                    $this->acceptTarget($sender);
                    break;
                case 3:
                    $this->rejectTarget($sender);
                    break;
                case 4:
                    $this->listLogic()->leaveFunction($sender);
                    break;
                case 5:
                    $this->removeTarget($sender);
                    break;
                case 6:
                    $this->onMenu($sender);
                    break;
                case 7:
                    break;
            }
        });
        $form->setTitle("§a» §8Quản lý thành viên §a«");
        $form->setContent($content);
        $form->addButton("Kick Thành Viên\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Chiêu Mộ Thành Viên\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Chấp Nhận Lời Mời\n§d§l»§r §7Tap to select!", 0, "textures/ui/check");
        $form->addButton("Từ Chối Lời Mowid\n§d§l»§r §7Tap to select!", 0, "textures/ui/cancel");
        $form->addButton("Rời Khỏi Đaoe\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Xoá Thành Viên\n§d§l»§r §7Tap to select!", 0, "textures/blocks/barrel_side");
        $form->addButton("Back\n§d§l»§r §7Tap to select!", 0, "textures/ui/book_arrowleft_hover");
        $form->addButton("§cExit", 0, "textures/blocks/barrier");
        $form->sendToPlayer($sender);
    }

    private $array = [
        "break",
        "craft",
        "place",
        "buy"
    ];

    /**
     * @param Player $player
     * @param $result_name
     */
    public function addQuests(Player $player, $result_name)
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) return;
            if ($data[1] === null) {
                $this->sendMessageForm($player, "Please change name");
                return;
            }
            if ($data[3] === null) {
                $this->sendMessageForm($player, "Please change item data");
                return;
            }
            if ($data[6] === null) {
                $this->sendMessageForm($player, "Please change reward command");
                return;
            }
            if ($data[4] === false) {
                $img_type = 0;
            } else {
                $img_type = 1;
            }
            if ($data[6] === null) {
                $explode = [];
            } else {
                $explode = explode(":", $data[6]);
            }
            $itemData = explode(":", $data[3]);
            $array = [
                "name" => $data[1],
                "questID" => time() . "-" . $data[1],
                "type" => $this->array[$data[2]],
                "progress" => 0,
                "item" => ["id" => (int)$itemData[0], "meta" => (int)$itemData[1], "amount" => (int)$itemData[2]],
                "type-img" => $img_type,
                "url-img" => $data[5],
                "rewardCommands" => $explode
            ];
            $this->getPlugin()->quests["quests"][] = $array;
            $this->sendMessageForm($player, "Success add quest with name: " . $data[1]);
        });
        $form->setTitle("Thêm nhiệm vụ");
        $form->addLabel('');
        $form->addInput("Input a Quest name", '', $result_name);
        $form->addDropdown("Chọn một loại nhiệm vụ", $this->array);
        $form->addInput("Nhập ID\nThí dụ: ID:META:AMOUNT", '', "1:0:10");
        $form->addToggle("Là Path? hoặc Url?", false);
        $form->addInput("Nhập nguồn img");
        $form->addInput("Nhập phần thưởng nhiệm vụ\nThí dụ: command1:command2:ect\n{player} gửi cho người chơi\n{name} sử dụng tên hiển thị", '', "givemoney {player} 1000:say {name} Chúc mừng");
        $form->sendToPlayer($player);
    }

    public function kickTarget($sender)
    {
        $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
        $playerData = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $value) {
            if ($value->getName() !== $sender->getName() and $value->getLevel()->getName() === $config['island']) {
                $playerData[] = $value->getName();
            }
        }
        if (count($playerData) < 1) {
            $this->sendMessageForm($sender, "Not found player in your island");
            return true;
        }
        $form = new CustomForm(function (Player $sender, $data) use ($playerData) {
            if (isset($data[1])) {
                $this->listLogic()->kickFunction($sender, $playerData[$data[1]]);
            }
            unset($playerData);
        });
        $form->setTitle("§a» §8Kick Thành Viên §a«");
        $form->addLabel('');
        $form->addDropdown("Chọn một người chơi", $playerData);
        $form->sendToPlayer($sender);
        $playerData = null;
    }

    public function inviteTarget($sender)
    {
        $playerData = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $value) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($value);
            if (empty($config["island"])) {
                if ($value->getName() !== $sender->getName()) {
                    $playerData[] = $value->getName();
                }
            }
        }
        if (count($playerData) < 1) {
            $this->sendMessageForm($sender, "Not found player empty island");
            return true;
        }
        $form = new CustomForm(function (Player $sender, $data) use ($playerData) {
            if (isset($data[1])) {
                $this->listLogic()->inviteFunction($sender, $playerData[$data[1]]);
            }
            unset($playerData);
        });
        $form->setTitle("§a» §8Chiêu Mộ thành Viên §a«");
        $form->addLabel('');
        $form->addDropdown("Chọn một người chơi", $playerData);
        $form->sendToPlayer($sender);
        $playerData = null;
    }

    public function acceptTarget($sender)
    {
        $form = new CustomForm(function (Player $sender, $data) {
            if (isset($data[1])) {
                $this->listLogic()->acceptFunction($sender, $data[1]);
            }
        });
        $form->setTitle("§a» §8Chấp Nhận Lời Mời §a«");
        $form->addLabel('');
        $form->addInput("Chọn tên người chơi", "Viết ở đây");
        $form->sendToPlayer($sender);
        $playerData = null;
    }

    public function rejectTarget($sender)
    {
        $form = new CustomForm(function (Player $sender, $data) {
            if (isset($data[1])) {
                $this->listLogic()->rejectFunction($sender, $data[1]);
            }
        });
        $form->setTitle("§a» §8Từ chối lời mời §a«");
        $form->addLabel('');
        $form->addInput("Chọn tên người chơi", "Viết ở đây");
        $form->sendToPlayer($sender);
        $playerData = null;
    }

    public function removeTarget($sender)
    {
        $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
        $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
        $playerData = [];
        if ($island instanceof Island) {
            foreach ($island->getMembers() as $value) {
                if ($value !== $sender->getName()) {
                    $playerData[] = $value;
                }
            }
        }
        if (count($playerData) < 1) {
            $this->sendMessageForm($sender, "Not found player in you members");
            return;
        }
        $form = new CustomForm(function (Player $sender, $data) use ($playerData) {
            if (isset($data[1])) {
                $this->listLogic()->removeFunction($sender, $playerData[$data[1]]);
            }
            unset($playerData);
        });
        $form->setTitle("§a» §8Xoá Thành Viên §a«");
        $form->addLabel('');
        $form->addDropdown("Chọn một người chơi", $playerData);
        $form->sendToPlayer($sender);
        $playerData = null;
    }

    public function makeLeader($sender)
    {
        $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($sender);
        $island = $this->getPlugin()->getIslandManager()->getOnlineIsland($config["island"]);
        $playerData = [];
        if ($island instanceof Island) {
            foreach ($island->getMembers() as $value) {
                if ($value !== $sender->getName()) {
                    $playerData[] = $value;
                }
            }
        }
        if (count($playerData) < 1) {
            $this->sendMessageForm($sender, "Not found player in you members");
            return;
        }
        $form = new CustomForm(function (Player $sender, $data) use ($playerData) {
            if (isset($data[1])) {
                $this->listLogic()->makeleaderFunction($sender, $playerData[$data[1]]);
            }
            unset($playerData);
        });
        $form->setTitle("§a» §8Chuyển Leader Đảo §a«");
        $form->addLabel('');
        $form->addDropdown("Chọn một người chơi", $playerData);
        $form->sendToPlayer($sender);
        $playerData = null;
    }

    public function visitIsland($sender)
    {
        $playerData = [];
        foreach (Server::getInstance()->getOnlinePlayers() as $value) {
            $config = $this->getPlugin()->getSkyBlockManager()->getPlayerConfig($value);
            if (!empty($config["island"])) {
                if ($value->getName() !== $sender->getName()) {
                    $playerData[] = $value->getName();
                }
            }
        }
        if (count($playerData) < 1) {
            $this->sendMessageForm($sender, "Not found player island");
            return;
        }
        $form = new CustomForm(function (Player $sender, $data) use ($playerData) {
            if (isset($data[1])) {
                $target = $this->getPlugin()->getServer()->getPlayer($playerData[$data[1]]);
                if ($target !== null) {
                    $this->listLogic()->tpFunction($sender, $target);
                }else{
                    $sender->sendMessage("§c Không tìm thấy player!. Sử dụng: /visit [playername] | /visit");
                }
            }
        });
        $form->setTitle("§a» §8Ghé thăm Đảo của người chơi §a«");
        $form->addLabel('');
        $form->addDropdown("Chọn một người chơi", $playerData);
        $form->sendToPlayer($sender);
        $playerData = null;
    }


    public function onProtect(): array
    {
        return array(
            '@',
            '#',
            '%',
            '&',
            '_',
            ';',
            "'",
            '"',
            ',',
            '~',
            '`',
            '|',
            '!',
            '$',
            '^',
            '*',
            '(',
            ')',
            '-',
            '+',
            '=',
            '{',
            '}',
            '[',
            ']',
            ':',
            '<',
            '>',
            '?',
            '.',
            '/',
        );
    }

    public function onReplace(): array
    {
        return array(
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        );
    }
}