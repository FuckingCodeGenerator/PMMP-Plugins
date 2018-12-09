<?php
namespace maze;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use maze\command\MainCommand;
use maze\event\{
	SignEvent,
	SignTouch
};

class TheMaze extends PluginBase
{
	/** @var array */
	private static $config = null;
	/** @var Config */
	public static $sign;

	public function onEnable()
	{
		$this->initConfig();
		$this->registerCommand();
		$this->registerEvents();
	}

	/**
	 * @return array
	 */
	public static function getConfigData() : array
	{
		return self::$config;
	}

	private function initConfig() : void
	{
		if (!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}
		self::$config = (new Config($this->getDataFolder() . "Config.yml", Config::YAML, [
			"WallBlock" => 1,
			"WallHeight" => 3,
			"TopWallBlock" => 41,
			"GroundBlock" => 0
		]))->getAll();
		self::$sign = new Config($this->getDataFolder() . "SignData.yml", Config::YAML);
	}

	private function registerEvents() : void
	{
		//$this->getServer()->getPluginManager()->registerEvents(new SignEvent, $this);
		//$this->getServer()->getPluginManager()->registerEvents(new SignTouch, $this);
	}

	private function registerCommand() : void
	{
		$this->getServer()->getCommandMap()->register("maze", new MainCommand());
	}
}
