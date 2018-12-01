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

namespace metowa1227\event;

use pocketmine\Player;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\scheduler\Task;

use metowa1227\StatusEditor;

class Receive implements Listener
{
    public function __construct($path, StatusEditor $owner)
    {
        $this->tmpfile = new Config($path . "tmp.yml", Config::YAML);
        $this->owner = $owner;
        if ($this->tmpfile->get("food-locked")) {
            $this->foodtask = $this->owner->getScheduler()->scheduleRepeatingTask(new Food(), 20);
        }
        if ($this->tmpfile->get("health-locked")) {
            $this->healtask = $this->owner->getScheduler()->scheduleRepeatingTask(new Health(), 20);
        }
    }

    public function send(Player $player, array $data, int $id) : void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if ($this->tmpfile->get("remove-effects")) {
            $player->removeAllEffects();
            return;
        }
        foreach ($this->tmpfile->getAll(true) as $key) {
            if ($key === "remove-effects") continue;
            $value = $this->tmpfile->get($key);
            $player->addEffect(new EffectInstance(Effect::getEffect($key), 99999999, $value, true));
        }
    }

    public function onReceived(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        if ($packet instanceof ModalFormResponsePacket) {
            $player   = $event->getPlayer();
            $name     = $player->getName();
            $formId   = $packet->formId;
            $server   = Server::getInstance();
            $formData = json_decode($packet->formData, true);
            switch ($formId) {
                case 19273561:
                    if (isset($this->difficulty[$name])) {
                        unset($this->difficulty[$name]);
                        if ($formData[0] === null) return;
                        $data = [
                            "type" => "modal",
                            "title" => "Are you sure?",
                            "content" => "\n\n本当に難易度を変更しますか？\n\n",
                            "button1" => "OK",
                            "button2" => "キャンセル"
                        ];
                        $this->send($player, $data, 5426346);
                        $this->tmp4[$name] = $formData[0];
                        $this->edit6[$name] = true;
                        return true;
                    }

                case 5426346:
                    if (isset($this->edit6[$name])) {
                        unset($this->edit6[$name]);
                        if ($formData) {
                            $server->setConfigInt("difficulty", $this->tmp4[$name]);
                            foreach ($server->getOnlinePlayers() as $online) {
                                $online->getLevel()->setDifficulty($this->tmp4[$name]);
                            }
                            $player->sendMessage(TextFormat::GREEN . "難易度を変更しました。");
                            unset($this->tmp4[$name]);
                            return true;
                        }
                    }

                case 1286351:
                    if (isset($this->gamemode[$name])) {
                        unset($this->gamemode[$name]);
                        if ($formData[0] === null) return;
                        $data = [
                            "type" => "modal",
                            "title" => "Are you sure?",
                            "content" => "\n\n本当にゲームモードを変更しますか？\n\n",
                            "button1" => "OK",
                            "button2" => "キャンセル"
                        ];
                        $this->send($player, $data, 5234556);
                        $this->tmp3[$name] = $formData[0];
                        $this->edit5[$name] = true;
                        return true;
                    }

                case 5234556:
                    if (isset($this->edit5[$name])) {
                        unset($this->edit5[$name]);
                        if ($formData) {
                            $server->setConfigInt("gamemode", $this->tmp3[$name]);
                            foreach ($server->getOnlinePlayers() as $online) {
                                $online->setGamemode($this->tmp3[$name]);
                            }
                            $player->sendMessage(TextFormat::GREEN . "ゲームモードを変更しました。");
                            unset($this->tmp3[$name]);
                            return true;
                        }
                    }

                case 3142341:
                    if (isset($this->fly[$name])) {
                        unset($this->fly[$name]);
                        if ($formData) {
                            $server->setConfigString("allow-flight", "off");
                            $player->sendMessage(TextFormat::GREEN . "フライを無効にしました。");
                            return true;
                        }
                    }

                case 87165243:
                    if (isset($this->fly[$name])) {
                        unset($this->fly[$name]);
                        if ($formData) {
                            $server->setConfigString("allow-flight", "on");
                            $player->sendMessage(TextFormat::GREEN . "フライを許可しました。");
                            return true;
                        }
                    }

                case 1234321:
                    if (isset($this->edit3[$name])) {
                        unset($this->edit3[$name]);
                        if ($formData[0] === "") return;
                        $data = [
                            "type" => "modal",
                            "title" => "Are you sure?",
                            "content" => "\n\nサーバー名を\"" . $formData[0] . "\"に変更します。\n\n",
                            "button1" => "OK",
                            "button2" => "キャンセル"
                        ];
                        $this->send($player, $data, 23654334);
                        $this->tmp2[$name] = $formData[0];
                        $this->edit4[$name] = true;
                        return true;
                    }

                case 23654334:
                    if (isset($this->edit4[$name])) {
                        unset($this->edit4[$name]);
                        if ($formData) {
                            $server->getNetwork()->setName($this->tmp2[$name]);
                            $server->setConfigString("motd", $this->tmp2[$name]);
                            $player->sendMessage(TextFormat::GREEN . "サーバー名を変更しました。");
                        }
                        unset($this->tmp2[$name]);
                        return true;
                    }

                case 8621543:
                    if (isset($this->edit[$name])) {
                        unset($this->edit[$name]);
                        if ($formData[0] !== "cancel") {
                            if (!ctype_digit($formData[0])) {
                                $content = [
                                    "type" => "input",
                                    "text" => "\nサーバーの参加人数を捏造します。\nキャンセルする場合は\"cancel\"と入力してください。\n\n",
                                    "placeholder" => "値は整数で入力してください。",
                                    "default" => ""
                                ];
                                $data[][] = [];
                                $data["type"] = "custom_form";
                                $data["title"] = "Editing";
                                $data["content"][] = $content;
                                $this->send($player, $data, 8621543);
                                $this->edit[$name] = true;
                                return true;
                            } else {
                                $this->tmp[$name] = $formData[0];
                                $data = [
                                    "type" => "modal",
                                    "title" => "Are you sure?",
                                    "content" => "\n\nサーバーの参加人数を捏造します。\nこれによりあなたのサーバーの信用度が低下する恐れがあります！\n\n",
                                    "button1" => "OK",
                                    "button2" => "キャンセル"
                                ];
                                $this->send($player, $data, 873265);
                                $this->edit2[$name] = true;
                                return true;
                            }
                        } else {
                            return true;
                        }
                    }

                case 873265:
                    if (isset($this->edit2[$name])) {
                        unset($this->edit2[$name]);
                        if ($formData) {
                            $server->getQueryInformation()->setPlayerCount($this->tmp[$name]);
                            $player->sendMessage(TextFormat::GREEN . "サーバー人数を変更しました。");
                        }
                        unset($this->tmp[$name]);
                        return true;
                    }

                case 7132645:
                    if (isset($this->white[$name])) {
                        unset($this->white[$name]);
                        if ($formData) {
                            $server->setConfigBool("white-list", false);
                            $player->sendMessage(TextFormat::GREEN . "ホワイトリストを解除しました。");
                        }
                        return true;
                    }

                case 5847326:
                    if (isset($this->white[$name])) {
                        unset($this->white[$name]);
                        if ($formData) {
                            $server->setConfigBool("white-list", true);
                            $player->sendMessage(TextFormat::GREEN . "ホワイトリストを有効にしました。");
                        }
                        return true;
                    }

                case 534535:
                    if (isset($this->remove[$name])) {
                        unset($this->remove[$name]);
                        if ($formData) {
                            foreach ($server->getOnlinePlayers() as $online) {
                                $online->removeAllEffects();
                            }
                            $this->tmpfile->set("remove-effects", true);
                            $this->tmpfile->save();
                            $player->sendMessage(TextFormat::GREEN . "エフェクトを削除しました。");
                            return true;
                        }
                    }

                case 2352562:
                    if (isset($this->edit10[$name])) {
                        unset($this->edit10[$name]);
                        if ($formData[0] !== "cancel") {
                            if (!ctype_digit($formData[0])) {
                                $content = [
                                    "type" => "input",
                                    "text" => "\nプレイヤー全員にエフェクト効果をかけます。\nエフェクトIDを入力してください。\nエフェクトID表:\nSPEED(移動速度上昇) => 1\nSLOWNESS(移動速度低下) => 2\nHASTE(採掘速度上昇) => 3\nMINING_FATIGUE(採掘速度低下) => 4\nSTRENGTH(攻撃力上昇) => 5\nINSTANT_HEALTH(即時回復) => 6\nINSTANT_DAMAGE(即時ダメージ) => 7\nJUMP_BOOST(飛躍力上昇) => 8\nNAUSEA(吐き気) => 9\nREGENERATION(再生能力) => 10\nRESISTANCE(耐性) => 11\nFIRE_RESISTANCE(火炎耐性) => 12\nWATER_BREATHING(水中呼吸) => 13\nINVISIBILITY(不可視) => 14\nBLINDNESS(盲目) => 15\nNIGHT_VISION(暗視) => 16\nHUNGER(空腹) => 17\nWEAKNESS(弱体化) => 18\nPOISON(毒) => 19\nWITHER(ウィザー) => 20\nHEALTH_BOOST(体力増強) => 21\nABSORPTION(衝撃吸収) => 22\nSATURATION(満腹度回復) => 23\nAIR_FLY(浮遊) => 24\n\n注意: これらのエフェクトはサーバー本体に実装されていない可能性があります！\n実装されていない場合は実行することができないのでご注意ください！\n\nキャンセルする場合は\"cancel\"と入力してください。\n\n",
                                    "placeholder" => "Effect IDは数字で入力してください。",
                                    "default" => ""
                                ];
                                $data[][] = [];
                                $data["type"] = "custom_form";
                                $data["title"] = "Typing";
                                $data["content"][] = $content;
                                for ($i = 0; $i <= 10; $i++) {
                                    $step[] = "" . $i . "";
                                }
                                $content = [
                                    'type' => "step_slider",
                                    'text' => "エフェクトの強さ(\"0\"で通常の強さ)",
                                    'steps' => $step,
                                    'defaultIndex' => "0"
                                ];
                                $data["content"][] = $content;
                                $this->send($player, $data, 2352562);
                                $this->edit10[$name] = true;
                                return true;
                            } else {
                                $this->tmp5[$name] = [$formData[0], $formData[1]];
                                $data = [
                                    "type" => "modal",
                                    "title" => "Are you sure?",
                                    "content" => "\n\nプレイヤー全員にエフェクト効果をかけます。\n\n",
                                    "button1" => "OK",
                                    "button2" => "キャンセル"
                                ];
                                $this->send($player, $data, 3763465);
                                $this->effect[$name] = true;
                                return true;
                            }
                        }
                    }

                case 43634563:
                    if (isset($this->food[$name])) {
                        unset($this->food[$name]);
                        if ($formData) {
                            $this->foodtask = $this->owner->getScheduler()->scheduleRepeatingTask(new Food(), 20);
                            $this->tmpfile->set("food-locked", true);
                            $this->tmpfile->save();
                            $player->sendMessage(TextFormat::GREEN . "ロックが完了しました。");
                            return true;
                        }
                    }

                case 5634634:
                    if (isset($this->foodu[$name])) {
                        unset($this->foodu[$name]);
                        if ($formData) {
                            $this->owner->getScheduler()->cancelTask($this->foodtask->getTaskId());
                            $this->tmpfile->set("food-locked", false);
                            $this->tmpfile->save();
                            $player->sendMessage(TextFormat::GREEN . "アンロックが完了しました。");
                            return true;
                        }
                    }

                case 7125437:
                    if (isset($this->heal[$name])) {
                        unset($this->heal[$name]);
                        if ($formData) {
                            $this->healtask = $this->owner->getScheduler()->scheduleRepeatingTask(new Health(), 20);
                            $this->tmpfile->set("health-locked", true);
                            $this->tmpfile->save();
                            $player->sendMessage(TextFormat::GREEN . "ロックが完了しました。");
                            return true;
                        }
                    }

                case 87654234:
                    if (isset($this->healu[$name])) {
                        unset($this->healu[$name]);
                        if ($formData) {
                            $this->owner->getScheduler()->cancelTask($this->healtask->getTaskId());
                            $this->tmpfile->set("health-locked", false);
                            $this->tmpfile->save();
                            $player->sendMessage(TextFormat::GREEN . "アンロックが完了しました。");
                            return true;
                        }
                    }

                case 6543465:
                    if (isset($this->kill[$name])) {
                        unset($this->kill[$name]);
                        if ($formData) {
                            foreach ($server->getOnlinePlayers() as $online) {
                                if ($online->getName() === $name) continue;
                                $online->kill();
                            }
                            $player->sendMessage(TextFormat::GREEN . "キルが完了しました。");
                        }
                        return true;
                    }

                case 23623454:
                    if (isset($this->kick[$name])) {
                        unset($this->kick[$name]);
                        if ($formData) {
                            foreach ($server->getOnlinePlayers() as $online) {
                                if ($online->getName() === $name) continue;
                                $online->kick();
                            }
                            $player->sendMessage(TextFormat::GREEN . "キックが完了しました。");
                        }
                        return true;
                    }

                case 114514931:
                    if (isset($this->whitelisted[$name])) {
                        unset($this->whitelisted[$name]);
                        if ($formData) {
                            foreach ($server->getOnlinePlayers() as $online) {
                                $online->setWhitelisted(true);
                            }
                            $player->sendMessage(TextFormat::GREEN . "登録が完了しました。");
                        }
                        return true;
                    }

                case 87124632:
                    if (isset($this->ef[$name])) {
                        unset($this->ef[$name]);
                        if ($formData === 1) {
                            $content = [
                                "type" => "input",
                                "text" => "\nプレイヤー全員にエフェクト効果をかけます。\nエフェクトIDを入力してください。\nエフェクトID表:\nSPEED(移動速度上昇) => 1\nSLOWNESS(移動速度低下) => 2\nHASTE(採掘速度上昇) => 3\nMINING_FATIGUE(採掘速度低下) => 4\nSTRENGTH(攻撃力上昇) => 5\nINSTANT_HEALTH(即時回復) => 6\nINSTANT_DAMAGE(即時ダメージ) => 7\nJUMP_BOOST(飛躍力上昇) => 8\nNAUSEA(吐き気) => 9\nREGENERATION(再生能力) => 10\nRESISTANCE(耐性) => 11\nFIRE_RESISTANCE(火炎耐性) => 12\nWATER_BREATHING(水中呼吸) => 13\nINVISIBILITY(不可視) => 14\nBLINDNESS(盲目) => 15\nNIGHT_VISION(暗視) => 16\nHUNGER(空腹) => 17\nWEAKNESS(弱体化) => 18\nPOISON(毒) => 19\nWITHER(ウィザー) => 20\nHEALTH_BOOST(体力増強) => 21\nABSORPTION(衝撃吸収) => 22\nSATURATION(満腹度回復) => 23\nAIR_FLY(浮遊) => 24\n\n注意: これらのエフェクトはサーバー本体に実装されていない可能性があります！\n実装されていない場合は実行することができないのでご注意ください！\n\nキャンセルする場合は\"cancel\"と入力してください。\n\n",
                                "placeholder" => "Effect ID...",
                                "default" => ""
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = "Typing";
                            $data["content"][] = $content;
                            for ($i = 0; $i <= 10; $i++) {
                                $step[] = "" . $i . "";
                            }
                            $content = [
                                'type' => "step_slider",
                                'text' => "エフェクトの強さ(\"0\"で通常の強さ)",
                                'steps' => $step,
                                'defaultIndex' => "0"
                            ];
                            $data["content"][] = $content;
                            $this->send($player, $data, 2352562);
                            $this->edit10[$name] = true;
                            return true;
                        } elseif ($formData === 2) {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nプレイヤー全員のエフェクト効果を削除します。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 534535);
                            $this->remove[$name] = true;
                            return true;
                        } elseif ($formData === 3) {
                            if ($this->tmpfile->get("food-locked")) {
                                $data = [
                                    "type" => "modal",
                                    "title" => "Are you sure?",
                                    "content" => "\n\nプレイヤー全員の空腹度固定を解除します。\n\n",
                                    "button1" => "OK",
                                    "button2" => "キャンセル"
                                ];
                                $this->send($player, $data, 5634634);
                                $this->foodu[$name] = true;
                                return true;
                            }
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nプレイヤー全員の空腹度を最大値で固定します。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 43634563);
                            $this->food[$name] = true;
                            return true;
                        } elseif ($formData === 4) {
                            if ($this->tmpfile->get("health-locked")) {
                                $data = [
                                    "type" => "modal",
                                    "title" => "Are you sure?",
                                    "content" => "\n\nプレイヤー全員の体力固定を解除します。\n\n",
                                    "button1" => "OK",
                                    "button2" => "キャンセル"
                                ];
                                $this->send($player, $data, 87654234);
                                $this->healu[$name] = true;
                                return true;
                            }
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nプレイヤー全員の体力を最大値で固定します。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 7125437);
                            $this->heal[$name] = true;
                            return true;
                        } elseif ($formData === 5) {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nあなた以外のプレイヤーを倒します。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 6543465);
                            $this->kill[$name] = true;
                            return true;
                        } elseif ($formData === 6) {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nあなた以外のプレイヤーをキックします。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 23623454);
                            $this->kick[$name] = true;
                            return true;
                        } elseif ($formData === 7) {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nサーバーにいるプレイヤーを全員ホワイトリストに登録します。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 114514931);
                            $this->whitelisted[$name] = true;
                            return true;
                        }
                    }

                case 3763465:
                    if (isset($this->effect[$name])) {
                        unset($this->effect[$name]);
                        if ($formData) {
                            foreach ($server->getOnlinePlayers() as $online) {
                                $online->addEffect(new EffectInstance(Effect::getEffect($this->tmp5[$name][0]), 99999999, $this->tmp5[$name][1], true));
                            }
                            $player->sendMessage(TextFormat::GREEN . "エフェクトを付与しました。");
                            $data = [$this->tmp5[$name][0] => $this->tmp5[$name][1]];
                            $this->tmpfile->set($this->tmp5[$name][0], $this->tmp5[$name][1]);
                            $this->tmpfile->set("remove-effects", false);
                            $this->tmpfile->save();
                        }
                        unset($this->tmp5[$name]);
                        return true;
                    }

                case 876532816:
                    if ($formData === 1) {
                        if ($server->hasWhitelist()) {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nホワイトリストを解除します。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 7132645);
                            $this->white[$name] = true;
                            return true;
                        } else {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nホワイトリストを有効にします。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 5847326);
                            $this->white[$name] = true;
                            return true;
                        }
                    } elseif ($formData === 2) {
                        $content = [
                            "type" => "input",
                            "text" => "\nサーバーの参加人数を捏造します。\nキャンセルする場合は\"cancel\"と入力してください。\n\n",
                            "placeholder" => "カスタム参加人数",
                            "default" => ""
                        ];
                        $data[][] = [];
                        $data["type"] = "custom_form";
                        $data["title"] = "Editing";
                        $data["content"][] = $content;
                        $this->send($player, $data, 8621543);
                        $this->edit[$name] = true;
                        return true;
                    } elseif ($formData === 3) {
                        $content = [
                            "type" => "input",
                            "text" => "\nサーバー名を変更します。\n\n",
                            "placeholder" => "サーバー名",
                            "default" => ""
                        ];
                        $data[][] = [];
                        $data["type"] = "custom_form";
                        $data["title"] = "Editing";
                        $data["content"][] = $content;
                        $this->send($player, $data, 1234321);
                        $this->edit3[$name] = true;
                        return true;
                    } elseif ($formData === 4) {
                        if ($server->getAllowFlight()) {
                            $data = [
                                "type" => "modal",
                                "title" => "Are you sure?",
                                "content" => "\n\nフライを無効にします。\n\n",
                                "button1" => "OK",
                                "button2" => "キャンセル"
                            ];
                            $this->send($player, $data, 3142341);
                            $this->fly[$name] = true;
                            return true;
                        }
                        $data = [
                            "type" => "modal",
                            "title" => "Are you sure?",
                            "content" => "\n\nフライを有効にします。\n\n",
                            "button1" => "OK",
                            "button2" => "キャンセル"
                        ];
                        $this->send($player, $data, 87165243);
                        $this->fly[$name] = true;
                        return true;
                    } elseif ($formData === 6) {
                        $content[] = [
                            "type" => "dropdown",
                            "text" => "\n\nゲームモード:\n",
                            "options" => [
                                "サバイバル",
                                "クリエイティブ",
                                "アドベンチャー",
                                "スペクテイター"
                            ],
                            "default" => null
                        ];
                        $data = [
                            "type" => "custom_form",
                            "title" => "Editing",
                            "content" => $content
                        ];
                        $this->send($player, $data, 1286351);
                        $this->gamemode[$name] = true;
                        return true;
                    } elseif ($formData === 5) {
                        $content[] = [
                            "type" => "dropdown",
                            "text" => "\n\n難易度:\n",
                            "options" => [
                                "ピースフル",
                                "イージー",
                                "ノーマル",
                                "ハード"
                            ],
                            "default" => null
                        ];
                        $data = [
                            "type" => "custom_form",
                            "title" => "Editing",
                            "content" => $content
                        ];
                        $this->send($player, $data, 19273561);
                        $this->difficulty[$name] = true;
                        return true;
                    } elseif ($formData === 7) {
                        $contents = array(
                            "閉じる",
                            "常時エフェクト",
                            "エフェクト削除",
                            "空腹度",
                            "体力",
                            "キル",
                            "キック",
                            "ホワイトリスト",
                        );
                        for ($i = 0; $i < 8; $i++) {
                            $buttons[] = [
                                "text" => $contents[$i],
                            ];
                        }
                        $data = [
                            "type"    => "form",
                            "title"   => "PlayerStatusEditor",
                            "content" => "\n\n選択してください:\n\n",
                            "buttons" => $buttons
                        ];
                        $this->send($player, $data, 87124632);
                        $this->ef[$name] = true;
                        return true;
                    }
            }
        }
    }
}

class Food extends Task
{
    public function __construct() {}

    public function onRun($tick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $online) {
            $online->setFood(20);
        }
    }
}

class Health extends Task
{
    public function __construct() {}

    public function onRun($tick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $online) {
            $online->setHealth($online->getMaxHealth());
        }
    }
}
