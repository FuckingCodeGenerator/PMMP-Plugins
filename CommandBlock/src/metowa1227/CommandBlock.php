<?php
namespace metowa1227;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\math\Vector3;

use korado531m7\InventoryMenuAPI\InventoryMenuAPI;

class CommandBlock extends PluginBase implements Listener
{
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$path = $this->getDataFolder();
		@mkdir($path);
		$this->saveData = new Config($path . "CommandBlocks.yml", Config::YAML);
	}

	public function onTouch(PlayerInteractEvent $event) : void
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if ($block->getId() !== Block::COMMAND_BLOCK)
			return;
		if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK)
			return;
		switch ($player->getGamemode()) {
			case 1:
				InventoryMenuAPI::sendInventoryMenu($player, [], "", InventoryMenuAPI::INVENTORY_TYPE_COMMAND_BLOCK);
				$this->blockData[$player->getName()] = $block;
				return;
			case 0:
				$this->runCommand($player, $block);
				return;
		}
	}

	public function onBreak(BlockBreakEvent $event)
	{
		$block = $event->getBlock();
		$x = round($block->x);
		$y = round($block->y);
		$z = round($block->z);
		$level = $block->getLevel()->getName();
		$pos = $x . ':' . $y . ':' . $z . ':' . $level;
		if (!$this->saveData->exists($pos))
			return;
		$this->saveData->remove($pos);
		$this->saveData->save();
	}

	private function runCommand(Player $player, $block)
	{
		$x = round($block->x);
		$y = round($block->y);
		$z = round($block->z);
		$level = $block->getLevel()->getName();
		$name = $player->getName();
		$pos = $x . ':' . $y . ':' . $z . ':' . $level;
		if (!$this->saveData->exists($pos))
			return;
		$data = $this->saveData->get($pos);
		if ($data["sender"] === "CONSOLE")
			$sender = new ConsoleCommandSender();
		else
			$sender = $player;
		$command = $data["command"];
		$keys = ["@s", "@p", "@r"];
		$nearest = $block->getLevel()->getNearestEntity(new Vector3($player->x, $player->y, $player->z), 100.00);
		if (empty($nearest))
			$nearest = $name;
		$online = $this->getServer()->getOnlinePlayers();
		$count = count($online);
		$rand = mt_rand(0, $count);
		$i = 0;
		$random = $player;
		foreach ($online as $players) {
			if ($i === $rand)
				$random = $players;
			$i++;
		}
		$replace = [$name, $nearest->getName(), $random->getName()];
		$result = str_replace($keys, $replace, $command);
		if (strpos($result, "@a") !== false || strpos($result, "@e") !== false) {  //Player only supported.
			foreach ($online as $players) {
				$result = str_replace(["@a", "@e"], [$name, $name], $result);
				$this->getServer()->dispatchCommand($players, $result);
			}
			return;
		}
		$this->getServer()->dispatchCommand($sender, $result);
	}

	public function onReceive(DataPacketReceiveEvent $event)
	{
		$packet = $event->getPacket();
		if ($packet instanceof CommandBlockUpdatePacket) {
			$player = $event->getPlayer();
			$name = $player->getName();
			if (!isset($this->blockData[$name]))
				return;
			$block = $this->blockData[$name];
			/**
			 * Since an incorrect value is returned, it is not used
				$x = round($packet->x);
				$y = round($packet->y);
				$z = round($packet->z);
			**/
			$x = round($block->x);
			$y = round($block->y);
			$z = round($block->z);
			$level = $block->getLevel()->getName();
			$pos = $x . ':' . $y . ':' . $z . ':' . $level;
			$sender = ($packet->isConditional) ? 'CONSOLE' : 'PLAYER';
			$this->saveData->set($pos, [
				"command" => $packet->command,
				"sender" => $sender
			]);
			$this->saveData->save();
			unset($this->blockData[$name]);
		}
	}
}
