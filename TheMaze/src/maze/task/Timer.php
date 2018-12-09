<?php
namespace maze\task;

use pocketmine\scheduler\Task;
use pocketmine\Player;

class Timer extends Task
{
	/** @var int */
	public $time;

	public function __construct(Player $player)
	{
		$this->player = $player;
	}

	public function onRun() : void
	{
		$this->time++;
	}
}