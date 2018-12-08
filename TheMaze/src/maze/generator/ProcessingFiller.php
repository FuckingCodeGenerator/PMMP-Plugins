<?php
namespace maze\generator;

class ProcessingFiller
{
	/**
	 * @param int $max
	 * @param int $min
	 *
	 * @return int
	 */
	protected final function getSide(int $max, int $min) : int
	{
		return ($max - $min) + 1;
	}

	/**
	 * @param int $start
	 * @param int $end
	 *
	 * @return int
	 */
	protected final function getNext(int $start, int $end) : int
	{
		return $start < $end ? 1 : -1;
	}
}
