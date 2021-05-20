<?php

namespace OguzhanUmutlu\AdminCore;

use OguzhanUmutlu\AdminCore\commands\BanCommand;
use OguzhanUmutlu\AdminCore\commands\IPBanCommand;
use OguzhanUmutlu\AdminCore\commands\MuteCommand;
use OguzhanUmutlu\AdminCore\commands\TempBanCommand;
use OguzhanUmutlu\AdminCore\commands\TempIpBanCommand;
use OguzhanUmutlu\AdminCore\commands\TempMuteCommand;
use OguzhanUmutlu\AdminCore\commands\UnBanCommand;
use OguzhanUmutlu\AdminCore\commands\UnIpBanCommand;
use OguzhanUmutlu\AdminCore\commands\UnMuteCommand;
use OguzhanUmutlu\AdminCore\commands\KickCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class AdminCore extends PluginBase {
    /*** @var AdminCore */
    private static $i;

    /*** @return AdminCore */
    public static function getInstance(): AdminCore {
        return self::$i;
    }

    public function onEnable() {
        self::$i = $this;
        $this->saveResource("messages.yml");
        $commands = array_filter((new Config($this->getDataFolder()."messages.yml"))->getAll(),function($n){return isset($n["enabled"]);});
        foreach($commands as $i => $x) {
            if($x["enabled"]) {
                $cmd = $this->getServer()->getCommandMap()->getCommand($x["name"]);
                if($cmd) $this->getServer()->getCommandMap()->unregister($cmd);
                switch($i) {
                    case "kick":
                        $this->getServer()->getCommandMap()->register($this->getName(), new KickCommand());
                        break;
                    case "ban":
                        $this->getServer()->getCommandMap()->register($this->getName(), new BanCommand());
                        break;
                    case "ipban":
                        $this->getServer()->getCommandMap()->register($this->getName(), new IPBanCommand());
                        break;
                    case "tempban":
                        $this->getServer()->getCommandMap()->register($this->getName(), new TempBanCommand());
                        break;
                    case "tempipban":
                        $this->getServer()->getCommandMap()->register($this->getName(), new TempIpBanCommand());
                        break;
                    case "unban":
                        $this->getServer()->getCommandMap()->register($this->getName(), new UnBanCommand());
                        break;
                    case "unipban":
                        $this->getServer()->getCommandMap()->register($this->getName(), new UnIpBanCommand());
                        break;
                    case "mute":
                        $this->getServer()->getCommandMap()->register($this->getName(), new MuteCommand());
                        break;
                    case "tempmute":
                        $this->getServer()->getCommandMap()->register($this->getName(), new TempMuteCommand());
                        break;
                    case "unmute":
                        $this->getServer()->getCommandMap()->register($this->getName(), new UnMuteCommand());
                        break;
                    default:
                        break;
                }
            }
        }
        new EventListener();
    }
}