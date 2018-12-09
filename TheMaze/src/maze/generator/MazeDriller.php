<?php
namespace maze\generator;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;
use maze\TheMaze;

class MazeDriller extends ProcessingDriller implements BaseData
{
	/**
	 * @param int   $startX
	 * @param int   $startZ
	 * @param int   $endX
	 * @param int   $endZ
	 * @param int   $posY
	 * @param Level $level
	 */
	public function drillWallToMaze(int $startX, int $startZ, int $endX, int $endZ, int $posY, Level $level) : void
	{
		$next = mt_rand(0, 3);
		while (true) {
			$x = mt_rand(min($startX, $endX), max($startX, $endX));
			$z = mt_rand(min($startZ, $endZ), max($startZ, $endZ));
			if ($x % 2 === 0 and $z % 2 === 0) {
				break;
			}
		}
		$this->drill($x, $posY, $z, TheMaze::getConfigData()["WallBlock"], $level, $next, $next);
	}

	/**
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 * @param int   $blockId
	 * @param Level $level
	 */
	private function drill(int $x, int $y, int $z, int $blockId, Level $level, int $next, int $backup) : void
	{
		while (true) {
			$nextX = $x + self::DRILL_MAZE_NEXT_DIRECTION[$next][0] * 2;
			$nextZ = $z + self::DRILL_MAZE_NEXT_DIRECTION[$next][1] * 2;
			$block = $level->getBlock(new Vector3($nextX, $y, $nextZ));
			$securityCheckX = $nextX + self::DRILL_MAZE_NEXT_DIRECTION[$next][0];
			$securityCheckZ = $nextZ + self::DRILL_MAZE_NEXT_DIRECTION[$next][1];
			if ($level->getBlock(new Vector3($nextX, $y + 1, $nextZ))->getId() !== $blockId or $level->getBlock(new Vector3($securityCheckX, $y + 1, $securityCheckZ))->getId() !== $blockId) {
				$next++;
				if ($next == 4) {
					$next = 0;
				}
				if ($next === $backup) {
					return;
				}
				continue;
			}
			for ($i = 1; $i <= 2; $i++) {
				for ($l = 1; $l < TheMaze::getConfigData()["WallHeight"]; $l++) {
					$level->setBlock(new Vector3($x + self::DRILL_MAZE_NEXT_DIRECTION[$next][0] * $i, $y + $l, $z + self::DRILL_MAZE_NEXT_DIRECTION[$next][1] * $i), Block::get(Block::AIR));
				}
			}
			$next = mt_rand(0, 3);
			$this->drill($nextX, $y, $nextZ, $blockId, $level, $next, $next);
		}
	}
}
