<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\Menu;


use BaliGamerz\SkyBlock\events\PlayerSuccessBuyEvent;
use BaliGamerz\SkyBlock\libraries\MadeForm\CustomForm;
use BaliGamerz\SkyBlock\libraries\MadeForm\SimpleForm;
use BaliGamerz\SkyBlock\Utils;

use pocketmine\item\Item;
use pocketmine\Player;

class ShopLogic extends PublicMenu
{

    public function getConfig(): array
    {
        return $this->getPlugin()->shops;
    }
public function onOpen($player)
    {
        $form = new SimpleForm(function (Player $player, $data) {
            if (is_null($data)) return;
            if ($data == 0) {
                $this->sendMessageForm($player, $this->getConfig()['exitmessage']);
            } else {
                $button = $data - 1;
                $list = array_keys($this->getConfig()['shop']);
                $shop = $list[$button];
                $this->onOpen2($player, $shop, $this->getConfig()['shop'][$shop]['name']);
            }
        });

        $name = $player->getName();
        $dataConfig = $this->getConfig();
        $form->setTitle($dataConfig['uititle']);
        $form->setContent(str_replace('{name}', $name, $dataConfig['uicontent']));
        $form->addButton($dataConfig['exitname'], $dataConfig['exittype'], $dataConfig['exitimg']);
        foreach (array_keys($dataConfig['shop']) as $shop) {
            $form->addButton("§b»§3 ".$dataConfig['shop'][$shop]['name']."\n§7Bấm để chọn!", $dataConfig['shop'][$shop]['type'], $dataConfig['shop'][$shop]['img']);
        }
        $form->sendToPlayer($player);
    }

    public function onOpen2($player, $sub, $title)
    {
        $form = new SimpleForm(function (Player $player, $data) use ($sub) {
            if (is_null($data)) return;
            if ($data == 0) {
                $player->sendMessage($this->getConfig()['exitmessage']);
            } else {
                $button = $data - 1;
                $list = array_keys($this->getConfig()['shop'][$sub]['item']);
                $shop = $list[$button];
                $data = $this->getConfig()['shop'][$sub]['item'][$shop];
                $this->openCount($player, $data['id'], $data['meta'], $data['price'], $data['name'], $data['img']);
            }
        });
        $dataShop = $this->getConfig();
        $form->setTitle(str_replace('{title}', $title, $dataShop['uisubtitle']));
        $form->setContent(str_replace('{name}', $player->getName(), $dataShop['uicontent']));
        $form->addButton($dataShop['backname'], $dataShop['backtype'], $dataShop['backimg']);
        foreach (array_keys($dataShop['shop'][$sub]['item']) as $item) {
            $form->addButton("§b»§3 ".$dataShop['shop'][$sub]['item'][$item]['name'] . "\n§cPrice: §7" . number_format($dataShop['shop'][$sub]['item'][$item]['price']),
                $dataShop['shop'][$sub]['item'][$item]['type'],
                $dataShop['shop'][$sub]['item'][$item]['img']
            );
        }
        $form->sendToPlayer($player);
    }


    public function openCount($player, $id, $meta, $price, $namee, $img)
    {
        $form = new CustomForm(function (Player $player, $data) use ($id, $meta, $price, $namee, $img) {
            if ($data !== null) {
                if (!is_numeric($data[1]) || (int)$data[1] < 0) {
                    $player->sendMessage('§cPlease enter an amount');
                    return;
                }
                $count = $data[1];
                $total = (int)$price * (int)$count;
                $this->openBuyItemConfirmationMenu($player, $id, $meta, $total, $count, $namee);
            }
        });
        $economy = $this->getPlugin()->economy;
        $form->setTitle($namee);
        $form->addLabel('§bSố Tiền của Bạn: §e' . self::onFormatMoney($economy->myMoney($player)));
        $form->addSlider('Vui lòng nhập số tiền: ', 1, 64, 1);
        $form->sendToPlayer($player);
    }


    public function openBuyItemConfirmationMenu(Player $player, $id, $meta, $cost, $count, $namee)
    {
        $economy = $this->getPlugin()->economy;
        $dataShop = $this->getConfig();
        $money = $economy->myMoney($player);
        if ($money < $cost) {
            $missing = (int)$money - (int)$cost;
            $str = str_replace('-', '', $missing);
            Utils::addSound($player, 400, 0.4);
            $this->sendMessageForm($player, str_replace(["{count}", "{item_name}", "{price}", "{missing}"], [$this->onFormatMoney($count), $namee, $this->onFormatMoney($cost), $this->onFormatMoney($str)], $dataShop['erorBuy']));
            return;
        }
        $economy->reduceMoney($player, $cost);
        Utils::addSound($player, 400);
        $this->sendMessageForm($player, str_replace(["{count}", "{item_name}"], [$this->onFormatMoney($count), $namee], $dataShop['successBuy']));
        $player->getInventory()->addItem(Item::get($id, $meta, $count));
        (new PlayerSuccessBuyEvent(["id" => $id, "meta" => $meta, "count" => $count], $player))->call();
    }



    /**
     * @param $number
     * @return string
     */
    public function onFormatMoney($number, $per = 0): string
    {
        $result = is_numeric($number) ? $number : 0;
        $integer = (int)$result;

        $key = [
            12 => 'T',
            9 => 'B',
            6 => 'M',
            3 => 'K',
            0 => ''
        ];
        foreach ($key as $exponent => $abbrev){
            if(abs($integer) >= pow(10, $exponent)){
                $display = $integer / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals).$abbrev;
                break;
            }
        }
        return $number;

    }
}
