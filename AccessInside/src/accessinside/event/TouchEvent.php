<?php
namespace accessinside\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use accessinside\form\OpenForm;
use accessinside\AccessInside;

class TouchEvent extends OpenForm implements Listener
{
	/** @var bool */
	protected static $processingRegister;
	/** @var Config */
	protected static $data;

	public function __construct($caller, string $path = "")
	{
		if ($caller instanceof AccessInside) {
			self::$data = new Config($path . "InsideBlocks.yml", Config::YAML);
		} elseif ($caller instanceof OpenForm) {
			//nothing to do.
		}
	}

	/**
	 * ロック付きインサイドブロックを登録する
	 *
	 * @param Player $player
	 *
	 * @return void
	*/
	public static function registerBlock(Player $player) : void
	{
		$name = $player->getName();
		$player->sendMessage(">> 登録したいブロックをタッチしてください。");
		self::$processingRegister[$name] = true;
	}

	public function onTouch(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
        $block = $event->getBlock();
        //破壊行為だったら
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
        	return;
        }
		//registerBlockの処理があるか
		if (isset(self::$processingRegister[$name])) {
			$this->continueProcessingRegister($player, $block);
			//バグ防止のため処理終了
			return;
		}
		//インサイドブロックかどうか
		if (!self::isInsideBlock($block)) {
			return;
		}
		$pos = floor($block->x) . " : " . floor($block->y) . " : " . floor($block->z) . " : " . $block->getLevel()->getName();
		$data = self::$data->get($pos);
		//パスワード入力が不要な設定の場合
		if ($data["DisableOwner"] && ($data["Owner"] === $name)) {
			$this->goToInside($player, $block);
			return;
		}
		//パスワード入力
		$this->openForm($player, OpenForm::ENTER_PASSWORD, $data, $block);
		return;
	}

	/**
	 * パスワード入力の続き
	 *
	 * @param Player $player
	 * @param Block  $block
	 * @param bool   $isSuccess
	 *
	 * @return void
	*/
	public function continueEnterPassword(Player $player, Block $block, bool $isSuccess) : void
	{
		if ($isSuccess) {
			$this->goToInside($player, $block);
			return;
		}
		$player->sendMessage("パスワードが一致しませんでした。");
	}

	/**
	 * インサイドブロックの登録の続き
	 *
	 * @param Player $player
	 * @param Block  $block
	 *
	 * @return void
	*/
	private function continueProcessingRegister(Player $player, Block $block) : void
	{
		$name = $player->getName();
		//既に生成済みだったら
		if (self::isInsideBlock($block)) {
			$player->sendMessage("既に登録済みのブロックです。");
			return;
		}
		//パスワードを決める
		$this->openForm($player, OpenForm::REGISTER_BLOCK, $block);
		return;
	}

	/**
	 * パスワード入力後の再処理
	 *
	 * @param Player $player
	 * @param        $password
	 * @param bool   $disableOwner
	 * @param Block  $block
	 *
	 * @return void
	*/
	public function reContinueProcessingRegister(Player $player, $password, bool $disableOwner, Block $block) : void
	{
		$name = $player->getName();
        //インサイドブロックの情報を保存
		$pos = floor($block->x) . " : " . floor($block->y) . " : " . floor($block->z) . " : " . $block->getLevel()->getName();
		$data = [
			"Password" => password_hash($password, PASSWORD_DEFAULT),
			"Owner" => $name,
			"DisableOwner" => (bool) $disableOwner
		];
		//データの保存
        self::$data->set($pos, $data);
        self::$data->save();
        $player->sendMessage("インサイドブロックを作成しました。");
        $player->sendMessage("パスワードは " . $password . " です。");
        $player->sendMessage("インサイドブロックの前後にはブロックを置かないようにしてください。");
        //一時データ削除
        unset(self::$processingRegister[$name]);
        return;
	}

	/**
	 * インサイドブロックかどうか調べる
	 *
	 * @param Block $block
	 *
	 * @return bool
	*/
	public static function isInsideBlock(Block $block) : bool
	{
		$pos = floor($block->x) . " : " . floor($block->y) . " : " . floor($block->z) . " : " . $block->getLevel()->getName();
		return self::$data->exists($pos);
	}

	/**
	 * BreakEvent用のデータ取得関数
	 *
	 * @return Config
	*/
	public static function getData() : Config
	{
		return self::$data;
	}

	/**
	 * データ削除
	 *
	 * @param $caller
	 * @param string $data
	*/
	public static function removeData($caller, $data) : void
	{
		if (!$caller instanceof BreakEvent) {
			return;
		}
		self::$data->remove($data);
		self::$data->save();
	}

	/**
	 * インサイドブロックを使用して中に入る
	 *
	 * @param Player $player
	 * @param Block  $block
	 *
	 * @return void
	*/
	private function goToInside(Player $player, Block $block) : void
	{
		switch ($player->getDirection()) {
			//東
			case 0:
				$pos = new Vector3($block->x + 2.2, $block->y, $block->z);
				break;
			//北
			case 1:
				$pos = new Vector3($block->x, $block->y, $block->z + 2.2);
				break;
			//西
			case 2:
				$pos = new Vector3($block->x - 2.2, $block->y, $block->z);
				break;
			//東
			case 3:
				$pos = new Vector3($block->x, $block->y, $block->z - 2.2);
				break;
		}
        $player->teleport($pos);
	}
}
