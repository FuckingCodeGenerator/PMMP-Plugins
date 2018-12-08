<?php
namespace maze\generator;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;
use maze\TheMaze;

class MazeDriller implements BaseData
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
		$startPosX = mt_rand(min($startX, $endX), max($startX, $endX));
		$startPosZ = mt_rand(min($startZ, $endZ), max($startZ, $endZ));
		for ($y = 0; $y < self::WALL_HEIGHT; $y++) {
			$level->setBlock(new Vector3($startPosX, $posY + $y, $startPosZ), Block::get(0));
		}
		$x = $startPosX;
		$z = $startPosZ;
		$next = mt_rand(0, 3);
		$backup = $next;
		$width = max($startX, $endX) - min($startX, $endX) - 1;
		$depth = max($startZ, $endZ) - min($startZ, $endZ) - 1;
		for ($px = 0; $px < $width; $px++) {
			for ($pz = 0; $pz < $depth; $pz++) {
				$direction = self::DRILL_MAZE_NEXT_DIRECTION[$next][0] * 2;
				$nextX = $x + $direction;
				$nextZ = $z + $direction;
				$nextVector = new Vector3($nextX, $posY, $nextZ);
				if ($level->getBlock($nextVector)->getId() !== Block::get(TheMaze::getConfigData()["WallBlock"])) {
					$next++;
					if ($next > 3) {
						$next = 0;
					}
					if ($next === $backup) {
						return;
					}
					continue;
				}
				for ($i = 1; $i <= 2; $i++) {
					for ($y = $posY; $y < self::WALL_HEIGHT; $y++) {
						$level->setBlock(new Vector3($x + self::DRILL_MAZE_NEXT_DIRECTION[$next][0] * $i, $y, $z + self::DRILL_MAZE_NEXT_DIRECTION[$next][0] * $i), Block::get(0));
					}
				}
			}
		}		
	}
}
