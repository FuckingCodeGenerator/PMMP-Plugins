<?php

/*
*  __  __       _                             __    ___    ___   _______
* |  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
* | |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
* | |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
* |_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
* All this program is made by hand of metowa1227.
* I certify here that all authorities are in metowa1227.
* Expiration date of certification: unlimited
* Secondary distribution etc are prohibited.
* The update is also done by the developer.
* This plugin is a developer API plugin to make it easier to write code.
* When using this plug-in, be sure to specify it somewhere.
* Warning if violation is confirmed.
*
* Developer: metowa1227
*/

/*
    Plugin description

    - CONTENTS
        - Server status editor

    - AUTHOR
        - metowa1227

    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Home 64bit
        - Intel(R) Core(TM) i7 6700 @ 3.40GHz
        - 16.00GB DDR4 SDRAM
        - PocketMine-MP 3.2.2
        - PHP 7.2.1 64bit supported version
*/

namespace metowa1227;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\{ Command, CommandSender };
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use metowa1227\event\Receive;

class StatusEditor extends PluginBase
{
	public function onEnable()
	{
		@mkdir($this->getDataFolder(), 0777, true);
		$this->getServer()->getPluginManager()->registerEvents(new Receive($this->getDataFolder(), $this), $this);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
	{
		if (!$sender instanceof Player) return false;
		$this->openWindow($sender);
		return true;
	}

    public function send(Player $player, array $data, int $id) : void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
    }

	public function openWindow(Player $player)
	{
        $contents = array(
        	"閉じる",
        	"ホワイトリスト",
        	"参加人数",
        	"サーバー名",
        	"フライ",
        	"難易度",
        	"ゲームモード",
        	"プレイヤー関連",
        );
        for ($i = 0; $i < 8; $i++) {
            $buttons[] = [
                "text" => $contents[$i],
            ];
        }
        $server = $this->getServer();
        $data = [
            "type"    => "form",
            "title"   => "StatusEditor",
            "content" => "\nStatus:\n\nOnline: " . count($server->getOnlinePlayers()) . " / " . $server->getMaxPlayers() . "\nWhiteList: " . ($server->hasWhitelist() ? "有効" : "無効") . "\nServerName: " . $server->getMotd() . "\n\n",
            "buttons" => $buttons
        ];
        $this->send($player, $data, 876532816);
	}
}