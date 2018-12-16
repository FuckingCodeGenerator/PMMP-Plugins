<?php
namespace driller;

use pocketmine\plugin\PluginBase;
use pocketmine\event\{
	Listener,
	block\BlockBreakEvent,
	player\PlayerJoinEvent
};
use pocketmine\utils\{
	Config,
	TextFormat
};
use pocketmine\scheduler\Task;
use xenialdan\BossBarAPI\API;

class Driller extends PluginBase implements Listener
{
	/** @var int */
	public $eid;

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if (!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}
		$this->data = new Config($this->getDataFolder() . "LevelData.yml", Config::YAML);
		$this->getScheduler()->scheduleRepeatingTask(new BossbarTask($this), 20);
	}

	public function onJoin(PlayerJoinEvent $event) : void
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		if (!$this->data->exists($name)) {
			$this->data->set($name, [
				"Level" => 1,
				"Need" => 10,
				"Now" => 0
			]);
		}
		$this->eid[$name] = mt_rand(1, PHP_INT_MAX);
	}

	public function onBreak(BlockBreakEvent $event) : void
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$before = $this->data->get($name);
		if ($before["Need"] < $before["Now"] + 1) {
			$before["Need"] = floor($before["Need"] * 1.5);
			$before["Now"] = 0;
			$player->addTitle(TextFormat::GREEN . "Levelup!", TextFormat::AQUA . TextFormat::ITALIC . "レベルが" . $before["Level"] . "から" . ++$before["Level"] . "に上がりました。");
		} else {
			$before["Now"]++;
		}
		$this->data->set($name, $before);
		$this->data->save();
	}

	public function sendBossbar()
	{
		$data = $this->data->getAll();
		foreach ($this->getServer()->getOnlinePlayers() as $online) {
			$name = $online->getName();
			if (!isset($this->eid[$name])) {
				continue;
			}
			$message = TextFormat::GREEN . "現在のレベル: " . $data[$name]["Level"];
			$percentage = floor($data[$name]["Now"] / $data[$name]["Need"] * 100);
		    API::sendBossBarToPlayer($online, $this->eid[$name], $message, $percentage);
		    API::setPercentage($percentage, $this->eid[$name]);
		}
	}
}

class BossbarTask extends Task
{
	public function __construct(Driller $owner)
	{
		$this->owner = $owner;
	}

	public function onRun(int $tick) : void
	{
		$this->owner->sendBossbar();
	}
}
