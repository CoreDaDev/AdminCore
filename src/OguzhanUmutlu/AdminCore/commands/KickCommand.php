<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;

class KickCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("kick.name"), "Kicks player from server", null, []);
        $this->setPermission("pocketmine.command.kick");
        $this->setPermissionMessage($this->translate("kick.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("kick.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("kick.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getPlayer($args[0]);
        $reason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "None";
        if(is_null($player)) {
            $sender->sendMessage($this->translate("kick.not-found"));
            return;
        }
        if($player instanceof Player)$player->kick(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("kick.kick-message")
        ), false);
        if($this->translate("kick.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{reason}", "{player}"],
                ["\n", $sender->getName(), $reason, $player->getName()],
                $this->translate("kick.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("kick.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("kick.success")
        ));
    }
}