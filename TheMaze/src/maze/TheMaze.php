<?php
namespace maze;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use maze\command\MainCommand;

class TheMaze extends PluginBase
{
	/** @var array */
	private static $config = null;

	public function onEnable()
	{
		$this->initConfig();
		$this->registerCommand();
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
		self::$config = (new Config($this->getDataFolder() . "Config.yml", Config::YAML, ["WallBlock" => 1, "WallHeight" => 3]))->getAll();
	}

	private function registerCommand() : void
	{
		$this->getServer()->getCommandMap()->register("maze", new MainCommand());
	}
}
