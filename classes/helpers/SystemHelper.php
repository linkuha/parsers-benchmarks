<?php

namespace helpers;

class SystemHelper
{
	/**
	 * Returns the Operating System.
	 *
	 * @return string OS, e.g. macosx, windows, linux.
	 */
	public static function getOS()
	{
		$uname = strtolower(php_uname());

		if (strpos($uname, "darwin") !== false) {
			return 'macosx';
		} elseif (strpos($uname, "win") !== false) {
			return 'windows';
		} elseif (strpos($uname, "linux") !== false) {
			return 'linux';
		} else {
			return 'unknown';
		}
	}
}