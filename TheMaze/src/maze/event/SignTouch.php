<?php
namespace maze\event;

use pocketmine\TextFormat;
use pocketmine\event\{
	Listener,
	player\PlayerInteractEvent
};
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use maze\path\Path;
use maze\memory\TaskingClipboard;
use metowa1227\moneysystem\api\core\API;

class SignTouch extends Path implements Listener
{
	public function onTouch(PlayerInteractEvent $event)
	{
		$block = $event->getBlock();
		$vector = $this->convertString($block);
		$sign = $this->getSign();
		if (!$sign->exists($vector)) {
			return;
		}
		$player = $event->getPlayer();
		$data = $sign->get($vector);
		if ($data["Type"] === "Goal") {
			$sign = $data["Prize"];
			$id = $data["ID"];
			foreach ($sign->getAll() as $all) {
				if ($all["ID"] === $id) {
					$startSignVectorX = $all["X"];
					$startSignVectorY = $all["Y"];
					$startSignVectorZ = $all["Z"];
					$startSignLevel = $all["Level"];
					break;
				}
			}
			if (!TaskingClipboard::isRunning($id, $player)) {
				return;
			}
			TaskingClipboard::stopTask($id, $player);
			$time = TaskingClipboard::getTime($id, $player);
			$player->sendMessage(TextFormat::GREEN . "迷路をクリアしました。");
			$api = API::getInstance();
			$api->increase($player, $prize);
			$player->sendMessage(TextFormat::GREEN . "時間: " . $time . " 報酬: " . $api->getUnit() . $prize);
			$teleportVector = new Vector3($startSignVectorX, $startSignVectorY, $startSignVectorZ);
			$player->teleport($teleportVector);
			TaskingClipboard::unregisterTask($id, $player);
		} else {
			if (TaskingClipboard::isRunning($id, $player)) {
				return;
			}
			$vector = new Vector3($data["X"], $data["Y"], $data["Z"]);
			//Stopped development on 2018/12/09
		}
	}
}
