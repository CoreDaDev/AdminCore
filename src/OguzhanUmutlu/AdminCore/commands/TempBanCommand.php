<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;

class TempBanCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("tempban.name"), "Temporarily bans player from server", null, []);
        $this->setPermission("pocketmine.command.ban.player");
        $this->setPermissionMessage($this->translate("tempban.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("tempban.no-perm"));
            return;
        }
        if(!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage($this->translate("tempban.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
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
                $sender->sendMessage($this->translate("tempban.invalid-time"));
                return;
        }
        if($player instanceof Player)$player->kick(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $player->getName(), $time],
            $this->translate("tempban.kick-message")
        ), false);
        $ban = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
        $banA = $ban->getAll();
        if(!isset($banA["player"])) $banA["player"] = [];
        $banA["player"][$player->getName()] = ["player" => $player->getName(), "reason" => $reason, "staff" => $sender->getName(), "expiresAt" => time()+$timeA];
        $ban->setAll($banA);
        $ban->save();
        $ban->reload();
        if($this->translate("tempban.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
                ["\n", $sender->getName(), $reason, $player->getName(), $time],
                $this->translate("tempban.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $player->getName(), $time],
            $this->translate("tempban.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}", "{time}"],
            ["\n", $sender->getName(), $reason, $player->getName(), $time],
            $this->translate("tempban.success")
        ));
    }
}