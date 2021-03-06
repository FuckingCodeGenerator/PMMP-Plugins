<?php
namespace maze\command;

use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\Player;
use maze\tmp\TmpStation;
use maze\generator\MazeGenerator;

class MainCommand extends Command
{
	public function __construct()
	{
        parent::__construct("maze", "Maze command", "/maze <pos1/pos2/generate>");
        $this->setPermission("maze.main");
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
    	if (!$sender instanceof Player) {
    		$sender->sendMessage("このコマンドはコンソールからは実行できません。");
    		return true;
    	}
    	if (!$sender->isOp()) {
    		$sender->sendMessage("このコマンドを実行する権限がありません。");
    		return true;
    	}
    	if (!isset($args[0])) {
    		$sender->sendMessage($this->getUsage());
    		return true;
    	}

    	// /maze [option]
    	switch ($args[0]) {
    		case "pos1":
    			$x = floor($sender->x);
    			$y = floor($sender->y);
    			$z = floor($sender->z);
    			$level = $sender->getLevel()->getName();
    			TmpStation::registerPos1($sender, $x, $y, $z, $level);
    			$sender->sendMessage("一つ目の地点を設定しました。");
    			return true;
    		case "pos2":
    			if (!TmpStation::isSettedPos1($sender)) {
    				$sender->sendMessage("まず一つ目の地点を設定してください。");
    				return true;
    			}
    			$x = floor($sender->x);
    			$z = floor($sender->z);
    			$level = $sender->getLevel()->getName();
    			$sender->sendMessage(TmpStation::registerPos2($sender, $x, $z, $level));
    			return true;
    		case "generate":
    			if (!TmpStation::isSettedPos2($sender)) {
    				$sender->sendMessage("まず範囲を設定してください。");
    				return true;
    			}
    			if (!MazeGenerator::generateMaze($sender)) {
    				$sender->sendMessage("迷路の生成に失敗しました。");
    				return true;
    			}
    			$sender->sendMessage("迷路を生成しました。");
    			$sender->sendMessage("§aGenerated by metowa1227.");
    			$sender->sendMessage("§bTheMaze 2018");
    			return true;
    		default:
    			$sender->sendMessage($this->getUsage());
    			return true;
    	}
	}
}
