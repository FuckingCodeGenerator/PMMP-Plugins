<?php
namespace accessinside\form;

use pocketmine\Player;
use pocketmine\Task;
use pocketmine\block\Block;
use accessinside\password\Password;
use accessinside\task\CoolDown;
use accessinside\AccessInside;
use accessinside\event\{
	TouchEvent,
	BreakEvent
};
use tokyo\pmmp\libform\{
    FormApi,
    element\Button,
    element\Input,
    element\Label,
    element\Toggle
};

class OpenForm implements Forms
{
	/** @var bool */
	public static $cooldown;
	/** @var Block */
	public $block;
	/** @var array */
	public $dataTmp;

	/**
	 * フォームを展開する
	 *
	 * @param Player $player
	 * @param int $type
	 *
	 * @return array | bool
	*/
	public function openForm(Player $player, int $type, $option, $secondOption = null)
	{
		$name = $player->getName();
		if (isset(self::$cooldown[$name])) {
			return;
		}
		self::$cooldown[$name] = true;
		AccessInside::getTaskScheduler()->scheduleDelayedTask(new CoolDown($name), 5);
		switch ($type) {
			case self::REGISTER_BLOCK:
				return $this->registerBlockForm($player, $option); //option: Block
			case self::ENTER_PASSWORD:
				return $this->enterPassword($player, $option, $secondOption); //option: array, secondOption: Block or BlockBreakEvent
		}
    }

    /**
     * ブロック登録フォームを展開する
     *
     * @param Player $player
     * @param Block  $block
     *
     * @return void
    */
    private function registerBlockForm(Player $player, Block $block) : void
    {
    	$this->block[$player->getName()] = $block;
        FormApi::makeCustomForm(
            function (Player $player, ?array $response) {
            	//フォームが閉じられているか
                if (FormApi::formCancelled($response)) {
                    return;
                }
                //パスワードが空だったら
                if ($response[0] === "") {
                	$player->sendMessage("パスワードを入力してください。");
                	return;
                }
                $touch = new TouchEvent($this);
                $touch->reContinueProcessingRegister($player, $response[0], $response[1], $this->block[$player->getName()]);
                unset($this->block[$player->getName()]);
                return;
            }
        )
        ->addElement(new Input("パスワードを入力してください。", "パスワード"))
        ->addElement(new Toggle("自分はパスワードを入力しなくても入れるようにするか\n注意: この機能を有効にすると安全性が低下します。", false))
        ->setTitle("ロック付きインサイドブロックを作成")
        ->sendToPlayer($player);
    }

    /**
     * パスワード入力フォーム
     *
     * @param Player $player
     * @param array  $data
     * @param Block | BlockBreakEvent $option
     *
     * @return void
    */
    private function enterPassword(Player $player, array $data, $option) : void
    {
    	$this->dataTmp[$player->getName()] = $data;
    	$this->blockTmp[$player->getName()] = $option;
        FormApi::makeCustomForm(
            function (Player $player, ?array $response) {
            	//フォームが閉じられているか
                if (FormApi::formCancelled($response)) {
                    return;
                }
                if ($this instanceof TouchEvent) {
	                $touch = new TouchEvent($this);
	                $touch->continueEnterPassword($player, $this->blockTmp[$player->getName()], Password::verifyPassword($this, $response[0], $this->dataTmp[$player->getName()]["Password"]));
	            } elseif ($this instanceof BreakEvent) {
	                $break = new BreakEvent();
	                $break->continueEnterPassword($player, $this->blockTmp[$player->getName()], Password::verifyPassword($this, $response[0], $this->dataTmp[$player->getName()]["Password"]));	            	
	            }
                unset($this->dataTmp[$player->getName()], $this->blockTmp[$player->getName()]);
            }
        )
        ->addElement(new Input("パスワードを入力してください。", "パスワード"))
        ->setTitle("パスワード入力")
        ->sendToPlayer($player);
    }
}
