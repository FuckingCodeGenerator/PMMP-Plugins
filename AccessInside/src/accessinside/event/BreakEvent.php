<?php
namespace accessinside\event;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\level\particle\DestroyBlockParticle;
use accessinside\form\OpenForm;
use accessinside\event\TouchEvent;

class BreakEvent extends OpenForm implements Listener
{
	/**
	 * パスワード入力の続き
	 *
	 * @param Player $player
	 * @param Event  $event
	 * @param bool   $isSuccess
	 *
	 * @return void
	*/
	public function continueEnterPassword(Player $player, BlockBreakEvent $event, bool $isSuccess) : void
	{
		if ($isSuccess) {
			$this->breaking = true;
			$block = $event->getBlock();
			$pos = floor($block->x) . " : " . floor($block->y) . " : " . floor($block->z) . " : " . $block->getLevel()->getName();
			TouchEvent::removeData($this, $pos);
			$block->getLevel()->setBlock(new Vector3($block->x, $block->y, $block->z), new Block(0, 0, 0));
			$block->getLevel()->addParticle(new DestroyBlockParticle($block->add(0.5, 0.5, 0.5), $block));
			$drops = $event->getDrops();
			if (!empty($drops)) {
				$dropPos = $block->add(0.5, 0.5, 0.5);
				foreach ($drops as $drop) {
					if (!$drop->isNull()) {
						$block->getLevel()->dropItem($dropPos, $drop);
					}
				}
			}
			$player->sendMessage("インサイドブロックを破壊しました。");
			return;
		}
		$player->sendMessage("パスワードが一致しませんでした。");
		return;
	}

	public function onBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		$block = $event->getBlock();
		//インサイドブロックかどうか
		if (!TouchEvent::isInsideBlock($block)) {
			return;
		}
		$pos = floor($block->x) . " : " . floor($block->y) . " : " . floor($block->z) . " : " . $block->getLevel()->getName();
		//データ取得
		$data = TouchEvent::getData()->get($pos);
		//作成者かどうか
		if ($data["Owner"] !== $name) {
			$player->sendMessage("他人のインサイドブロックを破壊することはできません。");
			$event->setCancelled();
			return;
		}
		//パスワード入力
		$this->openForm($player, OpenForm::ENTER_PASSWORD, $data, $event);
		$event->setCancelled();
		return;
	}
}
