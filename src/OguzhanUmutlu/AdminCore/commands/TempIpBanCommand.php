<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class TempIpBanCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("tempipban.name"), "Temporarily bans player from server", null, []);
        $this->setPermission("pocketmine.command.ban.player");
        $this->setPermissionMessage($this->translate("tempipban.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("tempipban.no-perm"));
            return;
        }
        if(!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage($this->translate("tempipban.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        $ip = $player->getAddress();
        $reason = isset($args[2]) ? implode(" ", array_slice($args, 2)) : "None";
        switch(substr($args[1], strlen($args[1])-1)) {
            case "m":
                $timeA = 60*(int)substr($args[1], 0, strlen($args[1])-1);
                $time = (int)substr($args[1], 0, strlen($args[1])-1)." minutes";
                break;
            case "h":
                $timeA = 60*60*(int)substr($args[1], 0, strlen($args[1])-1);
                $time = (int)substr($args[1], 0, strlen($args[1])-1)." hours";
                break;
            case "d":
                $timeA = 60*60*24*(int)substr($args[1], 0, strlen($args[1])-1);
                $time = (int)substr($args[1], 0, strlen($args[1])-1)." days";
                break;
            default:
                $sender->sendMessage($this->translate("tempipban.invalid-time"));
                return;
        }
        foreach($sender->getServer()->getOnlinePlayers() as $pl){
            if($ip === $pl->getAddress()) {
                $pl->kick(str_replace(
                    ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
                    ["\n", $sender->getName(), $reason, $pl->getName(), $time],
                    $this->translate("tempipban.kick-message")
                ), false);
                if($this->translate("tempipban.broadcast.enabled")) {
                    AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                        ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
                        ["\n", $sender->getName(), $reason, $pl->getName(), $time],
                        $this->translate("tempipban.broadcast.message")
                    ));
                }
                AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
                    ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
                    ["\n", $sender->getName(), $reason, $pl->getName(), $time],
                    $this->translate("tempipban.broadcast.message")
                ));
            }
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $ip, $time],
            $this->translate("tempipban.broadcast.message")
        ));
        $ban = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
        $banA = $ban->getAll();
        if(!isset($banA["ip"])) $banA["ip"] = [];
        $banA["ip"][$player->getName()] = ["ip" => $ip, "reason" => $reason, "staff" => $sender->getName(), "expiresAt" => time()+$timeA];
        $ban->setAll($banA);
        $ban->save();
        $ban->reload();
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $ip, $time],
            $this->translate("tempipban.success")
        ));
    }
}
