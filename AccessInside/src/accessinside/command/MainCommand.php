<?php
namespace accessinside\command;

use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\Player;
use accessinside\event\TouchEvent;

class MainCommand extends Command
{
    public function __construct()
    {
        parent::__construct("acin", "Register accessinside block", "/acin");
        $this->setPermission("command.acin");
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
    	if (!$sender instanceof Player) {
    		$sender->sendMessage("このコマンドはコンソールからは実行できません。");
    		return true;
    	}
    	TouchEvent::registerBlock($sender);
    	return true;
    }
}
