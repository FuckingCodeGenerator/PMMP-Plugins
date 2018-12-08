<?php
namespace maze\tmp;

use pocketmine\Player;

class TmpStation implements Messages
{
	/** @var array|null */
	private static $pos1, $pos2 = null;

	/**
	 * @param Player $player
	 * @param int    $x
	 * @param int    $y
	 * @param int    $z
	 * @param string $level
	 */
	public static function registerPos1(Player $player, int $x, int $y, int $z, string $level) : void
	{
		self::$pos1[$player->getName()] = [$x, $y, $z, $level];
	}

	/**
	 * @param Player $player
	 * @param int    $x
	 * @param int    $z
	 * @param string $level
	 */
	public static function registerPos2(Player $player, int $x, int $z, string $level) : string
	{
		$name = $player->getName();
		$pos1 = self::$pos1[$name];
		if ((($pos1[0] - $x) % 2 !== 0) or (($pos1[2] - $z) % 2 !== 0)) {
			return self::MESSAGE_FAILED_CAUSE_EVEN;
		}
		if ($pos1[3] !== $level) {
			return self::MESSAGE_FAILED_CAUSE_DIFF_LEVEL;
		}
		self::$pos2[$name] = [$x, $z, $level];
		return self::MESSAGE_SUCCESS;
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public static function isSettedPos1(Player $player) : bool
	{
		return isset(self::$pos1[$player->getName()]);
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public static function isSettedPos2(Player $player) : bool
	{
		return isset(self::$pos2[$player->getName()]);
	}

	/**
	 * @param Player $player
	 *
	 * @return array
	 */
	public static function getPos1(Player $player) : ?array
	{
		return self::$pos1[$player->getName()];
	}

	/**
	 * @param Player $player
	 *
	 * @return array
	 */
	public static function getPos2(Player $player) : ?array
	{
		return self::$pos2[$player->getName()];
	}
}
