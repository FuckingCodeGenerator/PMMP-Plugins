<?php

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
