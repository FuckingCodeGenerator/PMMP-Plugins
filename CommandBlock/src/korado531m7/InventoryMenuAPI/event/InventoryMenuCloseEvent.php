<?php

declare(strict_types=1);

namespace korado531m7\InventoryMenuAPI\event;

use pocketmine\Player;
use pocketmine\event\plugin\PluginEvent;

class InventoryMenuCloseEvent extends PluginEvent{
    private $who;
    private $name;

    /**
     * @param Player $who
     */
    public function __construct(Player $who, string $name){
        $this->who = $who;
        $this->name = $name;
    }

    /**
     * @return Player
     */
    public function getPlayer() : Player{
        return $this->who;
    }
    
    /**
     * @return string
     */
    public function getMenuName() : string{
        return $this->name;
    }
}
