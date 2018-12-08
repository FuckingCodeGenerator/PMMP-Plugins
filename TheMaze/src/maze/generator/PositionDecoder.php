<?php
namespace maze\generator;

class PositionDecoder
{
	/**
	 * @param array $pos1
	 * @param array $pos2
	 *
	 * @return array
	 */
	public function decodePos(array $pos1, array $pos2) : array
	{
		$startX = $pos1[0];
		$startZ = $pos1[2];
		$endX = $pos2[0];
		$endZ = $pos2[1];
        if ($startX > $endX) {
            $tmp = $endX;
            $endX = $startX;
            $startX = $tmp;
        }
        if ($startZ > $endZ) {
            $tmp = $endZ;
            $endZ = $startZ;
            $startZ = $tmp;
        }
        $startX--;
        $endX++;
        $startZ--;
        $endZ++;
        return [$startX, $pos1[1], $startZ, $endX, $endZ, $pos1[3]];
	}
}
