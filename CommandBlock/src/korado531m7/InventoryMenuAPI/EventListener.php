<?php
namespace korado531m7\InventoryMenuAPI;

use korado531m7\InventoryMenuAPI\event\InventoryMenuClickEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;

class EventListener implements Listener{
    private $plugin;
    
    public function __construct(InventoryMenuAPI $plugin){
        $this->plugin = $plugin;
    }
    
    public function onTransactionInventory(InventoryTransactionEvent $event){
        $object = $event->getTransaction()->getSource();
        if($object instanceof Player){
            if($this->plugin->isOpeningInventoryMenu($object)){
                $event->setCancelled();
                $this->plugin->restoreInventory($object);
            }
        }
    }
    
    public function onReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        switch(\get_class($pk)){
            case 'pocketmine\network\mcpe\protocol\ContainerClosePacket':
                $this->plugin->closeInventoryMenu($player);
            break;
                
            case 'pocketmine\network\mcpe\protocol\InventoryTransactionPacket':
                if($this->plugin->isOpeningInventoryMenu($player) && array_key_exists(0,$pk->actions)){
                    $action = $pk->actions[0];
                    $data = $this->plugin->getData($player);
                    $itemresult = $action->oldItem;
                    if($action->oldItem->getId() == 0) $itemresult = $action->newItem;
                    Server::getInstance()->getPluginManager()->callEvent(new InventoryMenuClickEvent($player, $itemresult,$data[4]));
                    $this->plugin->closeInventoryMenu($player);
                }
            break;
        }
    }
}