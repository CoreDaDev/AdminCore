<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class IPBanCommand extends BaseCommand {
    public function __construct() {
        parent::__construct($this->translate("ipban.name"), "Permanently bans player's IP from server", null, []);
        $this->setPermission("pocketmine.command.ban.ip");
        $this->setPermissionMessage($this->translate("ipban.no-perm"));
    }
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->translate("ipban.no-perm"));
            return;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->translate("ipban.usage"));
            return;
        }
        $player = AdminCore::getInstance()->getServer()->getOfflinePlayer($args[0]);
        $ip = $player->getAddress();
        $reason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "None";
        foreach($sender->getServer()->getOnlinePlayers() as $pl){
            if($ip === $pl->getAddress()){
                $pl->kick(str_replace(
                    ["{line}", "{staff}", "{reason}", "{player}"],
                    ["\n", $sender->getName(), $reason, $pl->getName()],
                    $this->translate("ipban.kick-message")
                ), false);
                if($this->translate("ipban.broadcast.enabled")) {
                    AdminCore::getInstance()->getServer()->broadcastMessage(str_replace(
                        ["{line}", "{staff}", "{reason}", "{player}"],
                        ["\n", $sender->getName(), $reason, $pl->getName()],
                        $this->translate("ipban.broadcast.message")
                    ));
                }
                AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
                    ["{line}", "{staff}", "{reason}", "{player}"],
                    ["\n", $sender->getName(), $reason, $pl->getName()],
                    $this->translate("ipban.broadcast.message")
                ));
            }
        }
        AdminCore::getInstance()->getServer()->getLogger()->log(0, str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $ip],
            $this->translate("ipban.broadcast.message")
        ));
        $ban = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
        $banA = $ban->getAll();
        if(!isset($banA["ip"])) $banA["ip"] = [];
        $banA["ip"][$player->getName()] = ["ip" => $ip, "reason" => $reason, "staff" => $sender->getName(), "expiresAt" => -1];
        $ban->setAll($banA);
        $ban->save();
        $ban->reload();
        $sender->sendMessage(str_replace(
            ["{line}", "{staff}", "{reason}", "{player}"],
            ["\n", $sender->getName(), $reason, $player ? $player->getName() : $ip],
            $this->translate("ipban.success")
        ));
    }
}
