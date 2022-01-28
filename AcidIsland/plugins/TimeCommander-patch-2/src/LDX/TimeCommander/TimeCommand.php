<?php
namespace LDX\TimeCommander;
use pocketmine\scheduler\Task;
use pocketmine\Server;
class TimeCommand extends Task {
  public function __construct(Main $plugin, $cmd) {
    $this->plugin = $plugin;
    $this->cmd = $cmd;
    $this->start = false;
  }
  public function onRun(int $ticks) {
    if($this->start) {
      $this->plugin->runCommand($this->cmd);
    } else {
      $this->start = true;
    }
  }
}
