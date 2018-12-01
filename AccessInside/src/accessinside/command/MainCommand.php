<?php
namespace accessinside\command;

use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\Player;
use pocketmine\utils\Config;
use accessinside\event\TouchEvent;

class MainCommand extends Command
{
    public function __construct(Config $config)
    {
        parent::__construct("acin", "Register accessinside block", "/acin");
        $this->setPermission("command.acin");
        $this->config = $config;
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
    	if (!$sender instanceof Player) {
    		$sender->sendMessage("このコマンドはコンソールからは実行できません。");
    		return true;
    	}
    	if ($this->config->get("op-only")) {
    		if (!$sender->isOp()) {
    			$sender->sendMessage("インサイドブロックはOPのみ設置可能です。");
    			return true;
    		}
    	}
    	TouchEvent::registerBlock($sender);
    	return true;
    }
}
