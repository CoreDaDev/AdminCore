<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class MuteCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("mute.name"), "Permanently mutes player from server", null, []);
        $this->setPermission("admincore.mute");
        $this->setPermissionMessage($this->translate("mute.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("mute.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("mute.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        $reason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "None";
        $mute = new Config(AdminCore::getInstance()->getDataFolder()."mutes.yml");
        $muteA = $mute->getAll();
        $muteA[$player->getName()] = ["player" => $player->getName(), "reason" => $reason, "staff" => $sender->getName(), "expiresAt" => -1];
        $mute->setAll($muteA);
        $mute->save();
        $mute->reload();
        if($this->translate("mute.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{reason}", "{player}"],
                ["\n", $sender->getName(), $reason, $player->getName()],
                $this->translate("mute.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("mute.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("mute.success")
        ));
    }
}