<?php
namespace maze\generator;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class WallFiller extends ProcessingFiller
{
	/**
	 * @param int   $blockId
	 * @param int   $startX
	 * @param int   $startZ
	 * @param int   $endX
	 * @param int   $endZ
	 * @param int   $posY
	 * @param Level $level
	 * @param int   $wallHeight
	 * @param int   $topBlockId
	 * @param int   $groundBlockId
	 */
	public function fillAllWithWall(int $blockId, int $startX, int $startZ, int $endX, int $endZ, int $posY, Level $level, int $wallHeight, int $topBlockId, int $groundBlockId) : void
	{
		if (!$blockId) {
			return;
		}
		$startX--;
		$maxX = max($startX, $endX);
		$maxZ = max($startZ, $endZ);
		$minX = min($startX, $endX);
		$minZ = min($startZ, $endZ);
		$sideX = $this->getSide($maxX, $minX);
		$sideZ = $this->getSide($maxZ, $minZ);
		$nextX = $this->getNext($startX, $endX);
		$nextZ = $this->getNext($startZ, $endZ);
		$sideZ -= 2;
		for ($i = 0; abs($i) <= $sideX; $i += $nextX) {
			$x = $startX + $i;
			for ($l = 0; $l < $wallHeight; $l++) {
				$y = $posY + $l;
				for ($j = 0; abs($j) < $sideZ; $j += $nextZ) {
					$z = $startZ + $j;
					$vec = new Vector3($x, $y, $z);
					if (($l - 1) < 0) {
						$level->setBlock($vec, Block::get($groundBlockId));
					} elseif ($l === ($wallHeight - 1)) {
						$level->setBlock($vec, Block::get($topBlockId));
					} else {
						$level->setBlock($vec, Block::get($blockId));
					}
				}
			}
		}
	}
}
