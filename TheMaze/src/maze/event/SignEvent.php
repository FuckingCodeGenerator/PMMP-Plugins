<?php
namespace maze\event;

use pocketmine\utils\TextFormat;
use pocketmine\event\{
	Listener,
	block\SignChangeEvent
};
use metowa1227\moneysystem\api\core\API;
use maze\path\Path;

class SignEvent extends Path implements Listener
{
	public function onSignChange(SignChangeEvent $event)
	{
		$player = $event->getPlayer();
		$sign = $this->getSign();
		$block = $event->getBlock();
		$lines = $event->getLines();
		if ($lines[0] === "[TheMaze]") {
			$prize = $lines[1];
			if (!ctype_digit($prize)) {
				$event->setLine(2, TextFormat::RED . "不正な値");
				return;
			}
			$name = $lines[2];
			$comment = $lines[3];
			$vector = $this->convertString($block);
			$sign->set($vector, [
				"Type" => "Start",
				"Prize" => $prize,
				"ID" => $name,
				"X" => $block->x,
				"Y" => $block->y,
				"Z" => $block->z,
				"Level" => $block->getLevel()->getName()
			]);
			$sign->save();
			$event->setLine(0, TextFormat::AQUA . TextFormat::BOLD . TextFormat::ITALIC . "[ THE MAZE ]");
			$event->setLine(1, TextFormat::GREEN . "報酬: " . API::getInstance()->getUnit() . $prize);
			$event->setLine(2, TextFormat::GREEN . "迷路名: " . $name);
			$event->setLine(3, $comment);
			$player->sendPopup(TextFormat::GREEN . "迷路看板を作成しました。");
			$player->sendMessage(TextFormat::YELLOW . "引き続きゴール看板も作成してください。");
		} elseif ($lines[0] === "[MazeGoal]") {
			$vector = $this->convertString($block);
			$id = $lines[2];
			$found = false;
			foreach ($sign->getAll() as $all) {
				if ($all["ID"] === $id) {
					$found = true;
					$prize = $all["Prize"];
					break;
				}
			}
			if (!$found) {
				$event->setLine(2, TextFormat::RED . "そのIDは存在しません。");
				return;
			}
			$sign->set($vector, [
				"Type" => "Goal",
				"ID" => $id
			]);
			$sign->save();
			$event->setLine(0, TextFormat::GREEN . TextFormat::BOLD . TextFormat::ITALIC . "[ MAZE GOAL ]");
			$event->setLine(2, TextFormat::YELLOW . "報酬: " . API::getInstance()->getUnit() . $prize);
		}
	}
}
