<?php

declare(strict_types=1);

namespace korado531m7\InventoryMenuAPI\event;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\plugin\PluginEvent;

class InventoryMenuClickEvent extends PluginEvent{
    private $who;
    private $name;
    private $item;

    /**
     * @param Player $who
     * @param Item   $item
     * @param string $name
     */
    public function __construct(Player $who, Item $item, string $name){
        $this->who = $who;
        $this->item = $item;
        $this->name = $name;
    }

    /**
     * @return Player
     */
    public function getPlayer() : Player{
        return $this->who;
    }
    
    /**
     * @return Item
     */
    public function getItem() : Item{
        return $this->item;
    }
    
    /**
     * @return string
     */
    public function getMenuName() : string{
        return $this->name;
    }
}
