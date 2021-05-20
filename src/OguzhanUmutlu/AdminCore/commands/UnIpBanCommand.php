<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class UnIpBanCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("unipban.name"), "Unbans IP from server", null, []);
        $this->setPermission("pocketmine.command.ban.player");
        $this->setPermissionMessage($this->translate("unipban.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("unipban.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("unipban.usage"));
            return;
        }
        $ip = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        $ban = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
        if(!isset($ban->getAll()["player"][$ip])) {
            $sender->sendMessage($this->translate("unipban.not-banned"));
            return;
        }
        $ban->removeNested("player.".$ip);
        $ban->save();
        $ban->reload();
        if($this->translate("unipban.broadcast.enabled")) {
            AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                ["{line}", "{staff}", "{ip}"],
                ["\n", $sender->getName(), $ip],
                $this->translate("unipban.broadcast.message")
            ));
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{ip}"],
            ["\n", $sender->getName(), $ip],
            $this->translate("unipban.broadcast.message")
        ));
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{ip}"],
            ["\n", $sender->getName(), $ip],
            $this->translate("unipban.success")
        ));
    }
}