<?php

declare(strict_types=1);

namespace korado531m7\InventoryMenuAPI\event;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\plugin\PluginEvent;

class InventoryMenuGenerateEvent extends PluginEvent{
    private $who;
    private $invType;
    private $items;
    private $name;

    /**
     * @param Player $who
     * @param string $name
     * @param Tile $tile
     */
    public function __construct(Player $who, array $items,int $invType, string $name){
        $this->who = $who;
        $this->items = $items;
        $this->invType = $invType;
        $this->name = $name;
    }

    /**
     * @return Player
     */
    public function getPlayer() : Player{
        return $this->who;
    }
    
    /**
     * @return int
     */
    public function getInventoryType() : int{
        return $this->invType;
    }
    
    /**
     * @return Item
     */
    public function getItems() : array{
        return $this->items;
    }
    
    /**
     * @return string
     */
    public function getMenuName() : string{
        return $this->name;
    }
}
