<?php

#               Copyright (C) 2016 MadeAja/BaliGamerz
##    ███╗░░░███╗░█████╗░██████╗░███████╗░█████╗░░░░░░██╗░█████╗░
##    ████╗░████║██╔══██╗██╔══██╗██╔════╝██╔══██╗░░░░░██║██╔══██╗
##    ██╔████╔██║███████║██║░░██║█████╗░░███████║░░░░░██║███████║
##    ██║╚██╔╝██║██╔══██║██║░░██║██╔══╝░░██╔══██║██╗░░██║██╔══██║
##    ██║░╚═╝░██║██║░░██║██████╔╝███████╗██║░░██║╚█████╔╝██║░░██║
##    ╚═╝░░░░░╚═╝╚═╝░░╚═╝╚═════╝░╚══════╝╚═╝░░╚═╝░╚════╝░╚═╝░░╚═╝


namespace BaliGamerz\SkyBlock\Task;


use BaliGamerz\SkyBlock\Main;
use BaliGamerz\SkyBlock\Menu\PublicMenu;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

/**
 * Using a thread to compress files is dumb, they do not often being used
 * for heavy tasks and it will be a waste of resources.
 */
class CompressionAsyncTask extends AsyncTask {

    /** @var string */
    private $data;

    /**
     * CompressionAsyncTask constructor.
     * @param array<int, string|bool> $data
     * @param Player $player
     * @param array $theme
     * @param $time
     * @param bool $bool
     */
    public function __construct(array $data, Player $player, array $theme, $time, $bool = true){
        $this->data = serialize($data);
        $this->storeLocal([$player, $time, $bool, $theme]);
    }

    public function onRun(){
        [$fromPath, $toPath, $compress] = $data = unserialize($this->data);

        if($compress){
            // "folder" "target.zip"
            self::compressFile($fromPath, $toPath); // Overwrites the whole zip file.
        }else{
            // "target.zip" "folder"
            self::decompressFile($fromPath, $toPath); // Overwrite the whole folder path.
        }
    }

    public static function compressFile(string $source, string $toPath): void{
        // Get real path for our folder
        $rootPath = realpath($source);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($toPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach($files as $name => $file){
            // Skip directories (they would be added automatically)
            if(!$file->isDir()){
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
                $zip->setCompressionName($filePath, ZipArchive::CM_BZIP2);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }

    public static function decompressFile(string $fromPath, string $toPath): void{
        // get the absolute path to $file
        $zip = new ZipArchive;
        $res = $zip->open($fromPath);

        if(!$res) return;

        // Force deleting the same arena name.
        AsyncDirectoryDelete::deleteDirectory($toPath);

        $zip->extractTo($toPath);
        $zip->close();
    }

    public function onCompletion(Server $server): void
    {
        [$call, $time, $bool, $theme] = $this->fetchLocal();
        if ($bool) {
            (new PublicMenu())->successCreateTheme($call, "Compression in §f" . round(microtime(true) - $time, 3) . "s", $theme);
        } else {
            Main::sendMessage($call, "Compression in §f" . round(microtime(true) - $time, 3) . "s");
        }
    }
}