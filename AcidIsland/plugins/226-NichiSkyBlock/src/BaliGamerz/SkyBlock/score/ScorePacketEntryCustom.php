<?php


namespace BaliGamerz\SkyBlock\score;


use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScorePacketEntryCustom extends ScorePacketEntry
{
    /**
     * ScorePacketEntryCustom constructor.
     * @param string $objectiveName
     * @param string $customName
     * @param int $scoreboardId
     */
    public function __construct(string $objectiveName, string $customName, int $scoreboardId)
    {
        $this->objectiveName = $objectiveName;
        $this->customName = $customName;
        $this->score = $scoreboardId;
        $this->scoreboardId = $scoreboardId;
        $this->type = 3;
    }

}