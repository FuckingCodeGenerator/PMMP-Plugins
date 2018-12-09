<?php
namespace maze\generator;

interface BaseData
{
	/** @var array */
	const DRILL_MAZE_NEXT_DIRECTION = [
		[0, 1],   //UP[NORTH]
		[0, -1],  //DOWN[SOUTH]
		[1, 0],   //LEFT[EAST]
		[-1, 0]   //RIGHT[WEST]
	];
}
