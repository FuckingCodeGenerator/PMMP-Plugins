<?php
namespace accessinside\task;

use pocketmine\scheduler\Task;
use accessinside\form\OpenForm;

class CoolDown extends Task
{
	/** @var string */
	private $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function onRun(int $tick)
	{
		unset(OpenForm::$cooldown[$this->name]);
	}
}
