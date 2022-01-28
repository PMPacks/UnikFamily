<?php

/*
 * BlockCMD - A PocketMine-MP plugin to block certain commands from being used by players
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/BlockCMD>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

declare(strict_types=1);

namespace BlockCMD;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class BlockCMD extends PluginBase implements Listener{
  
  /** @var Config */
  private $commands;
  
  public function onEnable() : void{
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
    $this->commands = new Config($this->getDataFolder()."commands.yml", Config::YAML);
    foreach($this->commands->getAll() as $command => $levels){
      $permission = new Permission("blockcmd.access." . $command, "Allows access to the " . $command . " command.", Permission::DEFAULT_FALSE);
      $this->getServer()->getPluginManager()->addPermission($permission);
    }
  }
  
  /**
   * @param CommandSender $sender
   * @param Command $cmd
   * @param string $label
   * @param array $args
   *
   * @return bool
   */
  public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
    if(!isset($args[0])) return false;
    
    $action = strtolower($args[0]);
    
    switch($action){
      case "add":
        if($sender->hasPermission("blockcmd") || $sender->hasPermission("blockcmd.command") || $sender->hasPermission("blockcmd.command.blockcmd") || $sender->hasPermission("blockcmd.command.blockcmd.add")){
          if(!isset($args[1])){
            $sender->sendMessage("Usage: /blockcmd add <command> [levels]");
            $sender->sendMessage("Command: The command to block.");
            $sender->sendMessage("Levels: Optional. Worlds where command is allowed (e.g world flat) ");
            return true;
          }
          
          $command = strtolower($args[1]);
          
          if(strpos($command, "/") !== false){
            $sender->sendMessage(TextFormat::RED . "Command must not contain the slash.");
            return true;
          }
          
          if($this->commands->exists($command)){
              $sender->sendMessage(TextFormat::RED . "Command is already blocked.");
              return true;
          }
          
          $permission = new Permission("blockcmd.access." . $command, "Allows access to the " . $command . " command.", Permission::DEFAULT_FALSE);
          $this->getServer()->getPluginManager()->addPermission($permission);
          
          if(isset($args[2])){
            $levels = [];
            foreach($args as $arg){
              if($arg === $args[0] or $arg === $args[1]){
                continue;
              }
              $levels[] = $arg;
            }
            $this->commands->set($command, $levels);
            $this->commands->save();
            $sender->sendMessage("Command " . TextFormat::GREEN . "/" . $command . TextFormat::WHITE . " has been blocked. Allowed levels: " . TextFormat::GREEN . implode(TextFormat::WHITE . ", " . TextFormat::GREEN, $levels));
            return true;
          }
          
          $this->commands->set($command, []);
          $this->commands->save();
          $sender->sendMessage("Command " . TextFormat::GREEN . "/" . $command . TextFormat::WHITE . " has been blocked.");
          return true;
        }
        $sender->sendMessage(TextFormat::RED . "You do not have permission to use this subcommand.");
        return true;
        break;
      case "list":
        if($sender->hasPermission("blockcmd") || $sender->hasPermission("blockcmd.command") || $sender->hasPermission("blockcmd.command.blockcmd") || $sender->hasPermission("blockcmd.command.blockcmd.list")){
          if(empty($this->commands->getAll())){
            $sender->sendMessage(TextFormat::RED . "No blocked commands found.");
            return true;
          }
          foreach($this->commands->getAll() as $command => $levels){
            $cmds[] = $command;
          }
          $sender->sendMessage("Blocked commands: " . TextFormat::GREEN . (implode(TextFormat::WHITE . ", " . TextFormat::GREEN, $cmds)));
          return true;
        }
        $sender->sendMessage(TextFormat::RED . "You do not have permission to use this subcommand.");
        return true;
        break;
      case "remove":
        if($sender->hasPermission("blockcmd") || $sender->hasPermission("blockcmd.command") || $sender->hasPermission("blockcmd.command.blockcmd") || $sender->hasPermission("blockcmd.command.blockcmd.remove")){ 
          if(!isset($args[1])){
            $sender->sendMessage("Usage: /blockcmd remove <command>");
            $sender->sendMessage("Command: The command to unblock.");
            return true;
          }
          
          $command = strtolower($args[1]);
          
          if(strpos($command, "/") !== false){
            $sender->sendMessage(TextFormat::RED . "Command must not contain the slash.");
            return true;
          }
          
          if($this->commands->exists($command)){
            $this->commands->remove($command);
            $this->commands->save();
            
            $this->getServer()->getPluginManager()->removePermission("blockcmd.access" . $command);
            
            $sender->sendMessage("Command " . TextFormat::GREEN . "/" . $command . TextFormat::WHITE . " has been unblocked.");
            return true;
          }
          
          $sender->sendMessage(TextFormat::RED . "Command is not blocked.");
          return true;
        }
        $sender->sendMessage(TextFormat::RED . "You do not have permission to use this subcommand.");
        return true;
        break;
      default:
        return false;
    }
  }
  
  /**
   * @param PlayerCommandPreprocessEvent $event
   */
  public function onPlayerCommandPreProcess(PlayerCommandPreprocessEvent $event) : void{
    $player = $event->getPlayer();
    $command = str_replace("/", "", explode(" ", strtolower($event->getMessage()))[0]);
    if($this->commands->exists($command)){
      $levels = $this->commands->get($command);
      
      if($player->hasPermission("blockcmd.access") || $player->hasPermission("blockcmd.access." . $command)){
        return;
      }
      if(!empty($levels)){
        if(in_array($player->getLevel()->getName(), $levels)){
          return;
        }
      }
      $player->sendMessage(TextFormat::RED . "You cannot use this command" . (empty($levels) ? "." : " here."));
      $event->setCancelled(true);
    }
  }
  
}
