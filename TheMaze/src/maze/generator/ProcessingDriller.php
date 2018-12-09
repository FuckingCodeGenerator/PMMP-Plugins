<?php
namespace maze\generator;

class ProcessingDriller
{
	/**
	 * @param int
	 */
	protected final function rand_odd(int $mod)
	{
	    $result = 1 + mt_rand(0, PHP_INT_MAX - 1) % $mod;
	    if ($result % 2 == 0) {
	        $result++;
	    }
	    if ($result > $mod) {
	        $result -= 2;
	    }
	    return $result;
	}
}
