<?php
namespace doublejump\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use doublejump\task\JumpTask;

class JumpEvent implements Listener
{
	public function onJump(PlayerToggleFlightEvent $event)
	{
		$player = $event->getPlayer();
		if ($player->getGamemode() === 1) {
			return;
		}
		$event->setCancelled();
    	$vector = $player->getDirectionVector();
        $player->setMotion(new Vector3($vector->x * 0.5, 0.3, $vector->z * 0.5));
		$player->setAllowFlight(false);
	}

    public function onMove(PlayerMoveEvent $event)
    {
    	$player = $event->getPlayer();
    	if (!$player->isFlying() && $player->onGround && $player->isSprinting()) {
    		$player->setAllowFlight(true);
    	}
    }
}
