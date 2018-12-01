<?php
namespace doublejump;

use pocketmine\plugin\PluginBase;
use doublejump\event\JumpEvent;

class DoubleJump extends PluginBase
{
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents(new JumpEvent(), $this);
	}
}
