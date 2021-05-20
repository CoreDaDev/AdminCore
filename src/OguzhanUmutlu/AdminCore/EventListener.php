<?php

namespace OguzhanUmutlu\AdminCore;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\utils\Config;

class EventListener implements Listener {
    public function __construct() {
        AdminCore::getInstance()->getServer()->getPluginManager()->registerEvents($this, AdminCore::getInstance());
    }
    public function onLogin(PlayerPreLoginEvent $e) {
        $player = $e->getPlayer();
        $bans = (new Config(AdminCore::getInstance()->getDataFolder()."bans.yml"))->getAll();
        if(!isset($bans["player"])) $bans["player"] = [];
        if(!isset($bans["ip"])) $bans["ip"] = [];
        if(isset($bans["player"][$player->getName()]) && $bans["player"][$player->getName()]["expiresAt"] < time() && $bans["player"][$player->getName()]["expiresAt"] != -1) {
            unset($bans["player"][$player->getName()]);
            $conf = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
            $conf->setAll($bans);
            $conf->save();
            $conf->reload();
        }
        if(isset($bans["ip"][$player->getAddress()]) && $bans["ip"][$player->getAddress()]["expiresAt"] < time() && $bans["ip"][$player->getAddress()]["expiresAt"] != -1) {
            unset($bans["ip"][$player->getAddress()]);
            $conf = new Config(AdminCore::getInstance()->getDataFolder()."bans.yml");
            $conf->setAll($bans);
            $conf->save();
            $conf->reload();
        }
        if(in_array($player->getName(), array_keys($bans["player"] ?? [])) || in_array($player->getAddress(), array_keys($bans["ip"] ?? []))) {
            $e->setCancelled(true);
            $e->setKickMessage("§cYou were banned.");
        }
    }
    public function onChat(PlayerChatEvent $e) {
        $player = $e->getPlayer();
        $mutes = (new Config(AdminCore::getInstance()->getDataFolder()."mutes.yml"))->getAll();
        if(isset($mutes[$player->getName()])) {
            if($mutes[$player->getName()]["expiresAt"] < time() && $mutes[$player->getName()]["expiresAt"] != -1) {
                unset($mutes[$player->getName()]);
                $conf = new Config(AdminCore::getInstance()->getDataFolder()."mutes.yml");
                $conf->setAll($mutes);
                $conf->save();
                $conf->reload();
            } else {
                $e->setCancelled(true);
                $player->sendMessage((new Config(AdminCore::getInstance()->getDataFolder()."messages.yml"))->getNested("mute-message"));
            }
        }
    }
}