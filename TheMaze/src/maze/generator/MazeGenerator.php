<?php
namespace maze\generator;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\{
	Player,
	Server
};
use maze\tmp\TmpStation;
use maze\TheMaze;

class MazeGenerator implements BaseData
{
	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public static function generateMaze(Player $player) : bool
	{
		$decoder = new PositionDecoder;
		$pos = $decoder->decodePos(TmpStation::getPos1($player), TmpStation::getPos2($player));
        $startX = $pos[0];
        $y = $pos[1];
        $startZ = $pos[2];
        $endX = $pos[3];
        $endZ = $pos[4];
        $level = Server::getInstance()->getLevelByName($pos[5]);
        $filler = new WallFiller;
        $filler->fillAllWithWall(TheMaze::getConfigData()["WallBlock"], $startX, $startZ, $endX, $endZ, $y, $level);
        $drill = new MazeDriller;
        $drill->drillWallToMaze($startX, $startZ, $endX, $endZ, $y, $level);
        return true;
	}
}
