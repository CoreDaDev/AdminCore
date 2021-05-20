<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class UnBanCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("unban.name"), "Unbans player from server", null, []);
        $this->setPermission("pocketmine.command.ban.player");
        $this->setPermissionMessage($this->translate("unban.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("unban.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("unban.usage"));
            return;
        }
        $player = $args[0];
        $ban = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
        if(!isset($ban->getAll()["player"][$player])) {
            $sender->sendMessage($this->translate("unban.not-banned"));
            return;
        }
        $ban->removeNested("player.".$player);
        $ban->save();
        $ban->reload();
        if($this->translate("unban.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{player}"],
                ["\n", $sender->getName(), $player],
                $this->translate("unban.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{player}"],
            ["\n", $sender->getName(), $player],
            $this->translate("unban.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{player}"],
            ["\n", $sender->getName(), $player],
            $this->translate("unban.success")
        ));
    }
}