<?php
namespace korado531m7\InventoryMenuAPI\task;

use korado531m7\InventoryMenuAPI\InventoryMenuAPI;
use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\item\Item;

class DelaySendInventoryTask extends Task{
    public function __construct($player,$items, $inventoryName,$inventoryType){
        $this->player = $player;
        $this->items = $items;
        $this->inventoryName = $inventoryName;
        $this->inventoryType = $inventoryType;
    }
    
    public function onRun(int $tick) : void{
        InventoryMenuAPI::sendInventoryMenu($this->player,$this->items, $this->inventoryName,$this->inventoryType);
    }
}
