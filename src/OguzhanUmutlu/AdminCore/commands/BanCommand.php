<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;

class BanCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("ban.name"), "Permanently bans player from server", null, []);
        $this->setPermission("pocketmine.command.ban.player");
        $this->setPermissionMessage($this->translate("ban.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("ban.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("ban.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        $reason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "None";
        if(is_null($player)) {
            $sender->sendMessage($this->translate("ban.not-found"));
            return;
        }
        if($player instanceof Player)$player->kick(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("ban.kick-message")
        ), false);
        $ban = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
        $banA = $ban->getAll();
        if(!isset($banA["player"])) $banA["player"] = [];
        $banA["player"][$player->getName()] = ["player" => $player->getName(), "reason" => $reason, "staff" => $sender->getName(), "expiresAt" => -1];
        $ban->setAll($banA);
        $ban->save();
        $ban->reload();
        if($this->translate("ban.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{reason}", "{player}"],
                ["\n", $sender->getName(), $reason, $player->getName()],
                $this->translate("ban.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("ban.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player->getName()],
            $this->translate("ban.success")
        ));
    }
}