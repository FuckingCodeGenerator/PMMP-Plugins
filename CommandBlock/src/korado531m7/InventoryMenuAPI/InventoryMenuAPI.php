<?php
namespace korado531m7\InventoryMenuAPI;

use korado531m7\InventoryMenuAPI\event\InventoryMenuCloseEvent;
use korado531m7\InventoryMenuAPI\event\InventoryMenuGenerateEvent;
use korado531m7\InventoryMenuAPI\task\DelayAddWindowTask;
use korado531m7\InventoryMenuAPI\task\DelaySendInventoryTask;
use korado531m7\InventoryMenuAPI\inventory\FakeInventory;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\plugin\PluginBase;

class InventoryMenuAPI extends PluginBase{
    private static $inventoryMenuVar = [];
    private static $inventory = [];
    private static $pluginbase;
    
    const INVENTORY_TYPE_CHEST = 1;
    const INVENTORY_TYPE_DOUBLE_CHEST = 2;
    const INVENTORY_TYPE_ENCHANTING_TABLE = 3;
    const INVENTORY_TYPE_HOPPER = 4;
    const INVENTORY_TYPE_BREWING_STAND = 5;
    const INVENTORY_TYPE_ANVIL = 6;
    const INVENTORY_TYPE_DISPENSER = 7;
    const INVENTORY_TYPE_DROPPER = 8;
    const INVENTORY_TYPE_BEACON = 9;
    const INVENTORY_TYPE_TRADING = 10; //Not implemented yet :(
    const INVENTORY_TYPE_COMMAND_BLOCK = 11; //Not implemented either
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        self::setPluginBase($this);
    }
    
    /**
     * Send an inventory menu to player
     *
     * @param Player  $player
     * @param Item[]  $items
     * @param string  $inventoryName
     * @param int     $inventoryType
     */
    public static function sendInventoryMenu(Player $player, array $items, $inventoryName = "Inventory Menu", $inventoryType = self::INVENTORY_TYPE_CHEST){
        if(self::isOpeningInventoryMenu($player)) return true;
        $x = (int) $player->x;
        $y = (int) $player->y + 4;
        $z = (int) $player->z;
        
        //if(count($items) === 0) $maxKey = 0; else $maxKey = max(array_keys($items));
        switch($inventoryType){
            default:
                throw new \InvalidArgumentException('Invalid Inventory Type');
            break;
            
            case self::INVENTORY_TYPE_DISPENSER:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::DISPENSER);
                $inv = new FakeInventory(WindowTypes::DISPENSER, new Vector3($x, $y, $z), [], 9);
            break;
            
            case self::INVENTORY_TYPE_DROPPER:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::DROPPER);
                $inv = new FakeInventory(WindowTypes::DROPPER, new Vector3($x, $y, $z), [], 9);
            break;
            
            case self::INVENTORY_TYPE_BEACON:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::BEACON);
                $inv = new FakeInventory(WindowTypes::BEACON, new Vector3($x, $y, $z), [], 1); //?
            break;
            
            case self::INVENTORY_TYPE_TRADING:
                throw new \RuntimeException('Trading Inventory is not supported on account of not impletented yet :(');
                //$inv = new FakeInventory(WindowTypes::TRADING, new Vector3($x, $y, $z), [], 27);
            break;
            
            case self::INVENTORY_TYPE_COMMAND_BLOCK:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::COMMAND_BLOCK);
                $inv = new FakeInventory(WindowTypes::COMMAND_BLOCK, new Vector3($x, $y, $z), [], 0);
            break;
            
            case self::INVENTORY_TYPE_CHEST:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::CHEST);
                $inv = new FakeInventory(WindowTypes::CONTAINER, new Vector3($x, $y, $z), [], 27);
            break;
            
            case self::INVENTORY_TYPE_ANVIL:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::ANVIL);
                $inv = new FakeInventory(WindowTypes::ANVIL, new Vector3($x, $y, $z), [], 27);
            break;
            
            case self::INVENTORY_TYPE_ENCHANTING_TABLE:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::ENCHANTING_TABLE);
                $inv = new FakeInventory(WindowTypes::ENCHANTMENT, new Vector3($x, $y, $z), [], 5);
            break;
            
            case self::INVENTORY_TYPE_BREWING_STAND:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::BREWING_STAND_BLOCK);
                $inv = new FakeInventory(WindowTypes::BREWING_STAND, new Vector3($x, $y, $z), [], 5); //5?
            break;
            
            case self::INVENTORY_TYPE_HOPPER:
                self::sendFakeBlock($player, $x, $y, $z, BlockIds::HOPPER_BLOCK);
                $inv = new FakeInventory(WindowTypes::HOPPER, new Vector3($x, $y, $z), [], 5);
            break;
                
            case self::INVENTORY_TYPE_DOUBLE_CHEST:
                self::sendFakeBlock($player, $x, $y, $z,BlockIds::CHEST);
                self::sendFakeBlock($player, $x, $y, $z + 1,BlockIds::CHEST);
                $tag = new CompoundTag();
                $tag->setInt('pairx', $x);
                $tag->setInt('pairz', $z);
                self::sendTagData($player, $tag, $x, $y, $z + 1);
                $inv = new FakeInventory(WindowTypes::CONTAINER, new Vector3($x, $y, $z), [], 54);
            break;
        }
        $tag = new CompoundTag();
        $tag->setString('CustomName', $inventoryName);
        self::sendTagData($player, $tag, $x, $y, $z);
        
        self::saveInventory($player);
        foreach($items as $itemkey => $item){
            $inv->setItem($itemkey,$item);
        }
        Server::getInstance()->getPluginManager()->callEvent(new InventoryMenuGenerateEvent($player,$items,$inventoryType,$inventoryName));
        switch($inventoryType){
            case self::INVENTORY_TYPE_ENCHANTING_TABLE:
            case self::INVENTORY_TYPE_HOPPER:
            case self::INVENTORY_TYPE_CHEST:
            case self::INVENTORY_TYPE_BREWING_STAND:
            case self::INVENTORY_TYPE_ANVIL:
            case self::INVENTORY_TYPE_DISPENSER:
            case self::INVENTORY_TYPE_DROPPER:
            case self::INVENTORY_TYPE_BEACON:
                self::$inventoryMenuVar[$player->getName()] = array($inventoryType,$x,$y,$z,$inventoryName);
            case self::INVENTORY_TYPE_COMMAND_BLOCK:
            case self::INVENTORY_TYPE_TRADING:
                $player->addWindow($inv);
            break;
            
            case self::INVENTORY_TYPE_DOUBLE_CHEST:
                self::$inventoryMenuVar[$player->getName()] = array(self::INVENTORY_TYPE_DOUBLE_CHEST,$x,$y,$z,$inventoryName);
                self::getPluginBase()->getScheduler()->scheduleDelayedTask(new DelayAddWindowTask($player,$inv), 10);
            break;
        }
    }
    
    /**
     * @param Player  $player
     * @param Item[]  $items
     * @param string  $inventoryName
     * @param int     $inventoryType
     */
    public static function fillInventoryMenu(Player $player, array $items, $inventoryName = "Fill Menu", $inventoryType = self::INVENTORY_TYPE_CHEST){
        self::closeInventoryMenu($player); //Need this?
        self::getPluginBase()->getScheduler()->scheduleDelayedTask(new DelaySendInventoryTask($player,$items, $inventoryName,$inventoryType), 10);
    }
    
    /**
     * Close an inventory menu if player is opening
     *
     * @param Player $player
     */
    public static function closeInventoryMenu(Player $player){
        if(!self::isOpeningInventoryMenu($player)) return true;
        $data = self::getData($player);
        Server::getInstance()->getPluginManager()->callEvent(new InventoryMenuCloseEvent($player, $data[4]));
        switch($data[0]){
            case self::INVENTORY_TYPE_DOUBLE_CHEST:
                self::sendFakeBlock($player,$data[1],$data[2],$data[3] + 1,BlockIds::AIR);
            case self::INVENTORY_TYPE_CHEST:
            case self::INVENTORY_TYPE_HOPPER:
            case self::INVENTORY_TYPE_BREWING_STAND:
            case self::INVENTORY_TYPE_ENCHANTING_TABLE:
            case self::INVENTORY_TYPE_ANVIL:
            case self::INVENTORY_TYPE_DISPENSER:
            case self::INVENTORY_TYPE_DROPPER:
            case self::INVENTORY_TYPE_BEACON:
            case self::INVENTORY_TYPE_TRADING:
            case self::INVENTORY_TYPE_COMMAND_BLOCK:
                self::sendFakeBlock($player,$data[1],$data[2],$data[3],BlockIds::AIR);
            break;
        }
        self::restoreInventory($player, true);
        unset(self::$inventoryMenuVar[$player->getName()]);
    }
    
    /**
     * Check whether player is opening inventory menu
     *
     * @param  Player $player
     * @return bool
     */
    public static function isOpeningInventoryMenu(Player $player) : bool{
        return array_key_exists($player->getName(),self::$inventoryMenuVar);
    }
    
    /**
     * @param Player  $player
     * @return array
     */
    public static function getData(Player $player) : array{
        return self::$inventoryMenuVar[$player->getName()] ?? [];
    }
    
    public static function saveInventory(Player $player){
        self::$inventory[$player->getName()] = $player->getInventory()->getContents();
    }
    
    public static function restoreInventory(Player $player, bool $reset = false){
        $inventory = self::$inventory[$player->getName()] ?? null;
        if($inventory === null) return false;
        $player->getInventory()->setContents($inventory);
        if($reset) unset($inventory[$player->getName()]);
    }
    
    private static function getPluginBase() : PluginBase{
        return self::$pluginbase;
    }
    
    private static function setPluginBase(PluginBase $plugin){
        self::$pluginbase = $plugin;
    }
    
    private static function sendTagData(Player $player, CompoundTag $tag, int $x, int $y, int $z){
        $writer = new NetworkLittleEndianNBTStream();
        $pk = new BlockEntityDataPacket;
        $pk->x = $x;
        $pk->y = $y;
        $pk->z = $z;
        $pk->namedtag = $writer->write($tag);
        $player->dataPacket($pk);
    }
    
    private static function sendFakeBlock(Player $player,int $x,int $y,int $z,int $blockid){
        $pk = new UpdateBlockPacket();
        $pk->x = $x;
        $pk->y = $y;
        $pk->z = $z;
        $pk->flags = UpdateBlockPacket::FLAG_ALL;
        $pk->blockRuntimeId = BlockFactory::toStaticRuntimeId($blockid);
        $player->dataPacket($pk);
    }
}