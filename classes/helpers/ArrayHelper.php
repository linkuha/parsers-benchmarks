<?php

namespace helpers;

class ArrayHelper
{
	private static $_tmpSearchValue;

	public static function arraySearchValByKeyRecursive($needkey, $haystack)
	{
		self::$_tmpSearchValue = 0;
		foreach($haystack as $key => $value) {
			self::$_tmpSearchValue = $value;
			if($needkey === $key OR (is_array($value) && self::arraySearchValByKeyRecursive($needkey, $value) !== false)) {
				return self::$_tmpSearchValue;
			}
		}
		return false;
	}

	/**
	 * Determines if an array is associative.
	 *
	 * This makes the assumption that input arrays are sequences or hashes.
	 * This assumption is a tradeoff for accuracy in favor of speed, but it
	 * should work in almost every case where input is supplied for a URI
	 * template.
	 *
	 * @param array $array Array to check
	 *
	 * @return bool
	 */
	private static function isAssoc(array $array)
	{
		return $array && array_keys($array)[0] !== 0;
	}
}