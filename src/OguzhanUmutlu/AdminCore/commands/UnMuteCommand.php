<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class UnMuteCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("unmute.name"), "Unmutes player from server", null, []);
        $this->setPermission("admincore.mute");
        $this->setPermissionMessage($this->translate("unmute.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("unmute.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("unmute.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        if(is_null($player)) {
            $sender->sendMessage($this->translate("unmute.not-found"));
            return;
        }
        $mute = new Config(AdminCore::getInstance()->getDataFolder()."mutes.yml");
        if(!isset($mute->getAll()[$player->getName()])) {
            $sender->sendMessage($this->translate("unmute.not-muted"));
            return;
        }
        $mute->removeNested($player->getName());
        $mute->save();
        $mute->reload();
        if($this->translate("unmute.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{player}"],
                ["\n", $sender->getName(), $player->getName()],
                $this->translate("unmute.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{player}"],
            ["\n", $sender->getName(), $player->getName()],
            $this->translate("unmute.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{player}"],
            ["\n", $sender->getName(), $player->getName()],
            $this->translate("unmute.success")
        ));
    }
}