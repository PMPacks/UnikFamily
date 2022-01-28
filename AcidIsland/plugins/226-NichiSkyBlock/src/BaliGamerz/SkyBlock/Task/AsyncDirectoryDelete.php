<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝



namespace BaliGamerz\SkyBlock\Task;


use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncDirectoryDelete extends AsyncTask {

    /** @var string */
    private $worldTable;

    /**
     * AsyncDirectoryDelete constructor.
     *
     * @param Level[]|null[] $worldToDelete
     */
    public function __construct(array $worldToDelete){
        $worlds = [];
        foreach($worldToDelete as $level){
            if($level === null) continue;
            if(!$level->isClosed()) Server::getInstance()->unloadLevel($level, true);

            $worlds[] = Server::getInstance()->getDataPath() . "worlds/" . $level->getFolderName();
        }
        $this->worldTable = serialize($worlds);
    }

    public function onRun(): void{
        $worldToDelete = unserialize($this->worldTable);

        foreach($worldToDelete as $level){
            self::deleteDirectory($level);
        }
    }

    public static function deleteDirectory(string $dir): bool{
        if(!file_exists($dir)){
            return true;
        }

        if(!is_dir($dir)){
            return unlink($dir);
        }

        foreach(scandir($dir) as $item){
            if($item == '.' || $item == '..'){
                continue;
            }

            if(!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)){
                return false;
            }

        }

        return rmdir($dir);
    }

    public function onCompletion(Server $server): void{
    }
}