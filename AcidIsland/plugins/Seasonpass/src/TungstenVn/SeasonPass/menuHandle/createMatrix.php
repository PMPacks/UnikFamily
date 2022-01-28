<?php

namespace TungstenVn\SeasonPass\menuHandle;

use TungstenVn\SeasonPass\menuHandle\menuHandle;

use pocketmine\item\Item;
class createMatrix
{

    public $matrix;

    public function __construct(menuHandle $mnh, $sender)
    {
        $this->createMatrix($mnh, $sender);
    }

    public function getMatrix()
    {
        return $this->matrix;
    }

    public function createMatrix(menuHandle $mnh, $sender)
    {
        $matrix = [];
        $max = $this->getMaxY($mnh);

        $normalPass = $mnh->cmds->ssp->getConfig()->getNested('normalPass');
        $royalPass = $mnh->cmds->ssp->getConfig()->getNested('royalPass');

        for ($x = 0; $x < 5; $x++) {
            for ($y = 0; $y < $max + 1; $y++) {
                if ($x == 0) {
                    if (isset($normalPass[$y])) {
                        $matrix[$x][$y] = $y;
                        if (isset($mnh->cmds->ssp->getConfig()->getNested("database")[$sender->getName()][0][$y])) {
                            $matrix[$x + 1][$y] = "taken";
                        } else {
                            $matrix[$x + 1][$y] = "none";
                        }
                    } else {
                        $matrix[$x][$y] = "n";
                    }
                } else if ($x == 1) {
                    if (isset($matrix[$x][$y])) {
                    } else {
                        $matrix[$x][$y] = "n";
                    }
                } else if ($x == 2) {
                    $matrix[$x][$y] = "crossline";
                } else if ($x == 3) {
                    if (isset($royalPass[$y])) {
                        $matrix[$x][$y] = $y;
                        if (isset($mnh->cmds->ssp->getConfig()->getNested("database")[$sender->getName()][1][$y])) {
                            $matrix[$x + 1][$y] = "taken";
                        } else {
                            $matrix[$x + 1][$y] = "none";
                        }
                    } else {
                        $matrix[$x][$y] = "n";
                    }
                } else {
                    if (isset($matrix[$x][$y])) {
                    } else {
                        $matrix[$x][$y] = "n";
                    }
                }
            }
        }
        $this->matrix = $matrix;
    }

    public function getMaxY(menuHandle $mnh)
    {
        $arr1 = $mnh->cmds->ssp->getConfig()->getNested('normalPass');
        $arr2 = $mnh->cmds->ssp->getConfig()->getNested('royalPass');
        $max1 = 0;
        $max2 = 0;
        if (is_array($arr1) && count($arr1) > 0) {
            $max1 = max(array_keys($arr1));
        } else {
            $max1 = 0;
        }

        if (is_array($arr2) &&  count($arr2) > 0) {
            $max2 = max(array_keys($arr2));
        } else {
            $max2 = 0;
        }
        return ($max1 > $max2) ? $max1 : $max2;

    }
}