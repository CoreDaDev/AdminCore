<?php

namespace OguzhanUmutlu\AdminCore\commands;

use OguzhanUmutlu\AdminCore\AdminCore;
use pocketmine\command\Command;
use pocketmine\utils\Config;

abstract class BaseCommand extends Command {
    public function translate(string $key): string {
        return (new Config(AdminCore::getInstance()->getDataFolder()."messages.yml"))->getNested($key) ?? "Invalid translation. (".$key.")";
    }
}