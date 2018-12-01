<?php
namespace korado531m7\InventoryMenuAPI\task;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\item\Item;

class DelayAddWindowTask extends Task{
    public function __construct(Player $player,$inventory){
        $this->who = $player;
        $this->inventory = $inventory;
    }
    
    public function onRun(int $tick) : void{
        $this->who->addWindow($this->inventory);
    }
}
