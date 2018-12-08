<?php
namespace maze\generator;

interface BaseData
{
	/** @var int */
	const WALL_HEIGHT = 3;
	/** @var array */
	const DRILL_MAZE_NEXT_DIRECTION = [
		[0, -1],   //UP
		[0, 1],  //DOWN
		[-1, 0],   //LEFT
		[1, 0]   //RIGHT
	];
}
