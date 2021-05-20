<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class TempMuteCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("tempmute.name"), "Temporarily mutes player from server", null, []);
        $this->setPermission("admincore.mute");
        $this->setPermissionMessage($this->translate("tempmute.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("tempmute.no-perm"));
            return;
        }
        if(!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage($this->translate("tempmute.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        $reason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "None";
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
                $sender->sendMessage($this->translate("tempban.invalid-time"));
                return;
        }
        if(is_null($player)) {
            $sender->sendMessage($this->translate("tempmute.not-found"));
            return;
        }
        $mute = new Config(AdminCore::getInstance()->getDataFolder()."mutes.yml");
        $muteA = $mute->getAll();
        $muteA[$player->getName()] = ["player" => $player->getName(), "reason" => $reason, "staff" => $sender->getName(), "expiresAt" => time()+$timeA];
        $mute->setAll($muteA);
        $mute->save();
        $mute->reload();
        if($this->translate("tempmute.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
                ["\n", $sender->getName(), $reason, $player->getName(), $time],
                $this->translate("tempmute.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $player->getName(), $time],
            $this->translate("tempmute.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $player->getName(), $time],
            $this->translate("tempmute.success")
        ));
    }
}