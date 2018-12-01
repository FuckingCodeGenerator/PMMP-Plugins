<?php
/**
 * インサイドブロックとは、このプラグインで使用されるパスワード付きのブロックで、パスワードを解除することにより中に入れるというものです。
 * 勝手に決めました。
*/

namespace accessinside;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
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
		if (!is_dir($this->getDataFolder())) {
			@mkdir($this->getDataFolder());
		}
		//イベント登録
		$this->getServer()->getPluginManager()->registerEvents(new TouchEvent($this, $this->getDataFolder()), $this);
		$this->getServer()->getPluginManager()->registerEvents(new BreakEvent(), $this);
		//Config生成
		$config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, ["op-only" => true]);
		//コマンド登録
		$this->getServer()->getCommandMap()->register("acin", new MainCommand($config));
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
