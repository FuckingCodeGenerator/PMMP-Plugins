<?php
namespace maze\path;

use pocketmine\utils\Config;
use pocketmine\block\Block;
use maze\TheMaze;

class Path
{
	/**
     * @return string
     */
	protected final function getPath() : string
	{
		return TheMaze::getPath();
	}

	/**
	 * @return Config [object]
	 */
	protected function getSign() : Config
	{
		return TheMaze::$sign;
	}

	/**
	 * @param Block $block
	 *
	 * @return string
	 */
	protected function convertString(Block $block) : string
	{
		return (string) floor($block->x) . " : " . floor($block->y) . " : " . floor($block->z) . " : " . $block->getLevel()->getName();
	}
}
