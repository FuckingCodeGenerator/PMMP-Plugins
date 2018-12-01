<?php
namespace accessinside\password;

use accessinside\event\{
	TouchEvent,
	BreakEvent
};

class Password
{
	/**
	 * パスワードが一致するか検証する
	 *
	 * @param object $caller
	 * @param $password
	 * @param $passwordData
	 *
	 * @return bool
	*/
	public static function verifyPassword($caller, $password, $passwordData) : bool
	{
		if (!$caller instanceof TouchEvent && !$caller instanceof BreakEvent) {
			return false;
		}
		return password_verify($password, $passwordData);
	}
}
