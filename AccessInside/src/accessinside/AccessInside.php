<?php
/**
 * インサイドブロックとは、このプラグインで使用されるパスワード付きのブロックで、パスワードを解除することにより中に入れるというものです。
 * 勝手に決めました。
*/

namespace accessinside;

use pocketmine\plugin\PluginBase;
use accessinside\event\{
	TouchEvent,
	BreakEvent
};
use accessinside\command\MainCommand;

class AccessInside extends PluginBase
{
	/** @var TaskScheduler */
	private static $scheduler;

	public function onEnable()
	{
		//イベント登録
		$this->getServer()->getPluginManager()->registerEvents(new TouchEvent($this, $this->getDataFolder()), $this);
		$this->getServer()->getPluginManager()->registerEvents(new BreakEvent(), $this);
		//コマンド登録
		$this->getServer()->getCommandMap()->register("acin", new MainCommand());
		self::$scheduler = $this->getScheduler();
	}

	/**
	 * スケジューラを取得する
	*/
	public static function getTaskScheduler()
	{
		return self::$scheduler;
	}
}
