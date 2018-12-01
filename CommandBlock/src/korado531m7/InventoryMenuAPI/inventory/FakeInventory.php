<?php
namespace korado531m7\InventoryMenuAPI\inventory; 

use pocketmine\math\Vector3;
use pocketmine\inventory\CustomInventory;

class FakeInventory extends CustomInventory{

    /** @var int */
    protected $network_type;
    protected $title;
    protected $size;

    public function __construct(int $network_type, Vector3 $holder, array $items = [], int $size = null, string $title = 'Inventory Menu'){
        $this->network_type = $network_type;
        $this->title = $title;
        $this->size = $size;
        parent::__construct($holder, $items, $size, $title);
    }

    public function getNetworkType() : int{
        return $this->network_type;
    }
    
    public function getName() : string{
        return $this->title;
    }
    
    public function getDefaultSize() : int{
        return $this->size;
    }
}