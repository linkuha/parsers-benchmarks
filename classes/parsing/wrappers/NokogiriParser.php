<?php

namespace parsing\wrappers;

use nokogiri;

class NokogiriParser
{
	private static $tmp;

	public static function recursive_array_search($needkey, $haystack)
	{
		self::$tmp = 0;
		foreach($haystack as $key => $value) {
			self::$tmp = $value;
			if($needkey === $key OR (is_array($value) && self::recursive_array_search($needkey, $value) !== false)) {
				return self::$tmp;
			}
		}
		return false;
	}

	public static function parseLinks($html)
	{
		// Creates DOMDocument of version 1.0 encoding UTF-8
		$dom = new nokogiri($html);
		$links = array();
		foreach($dom->get('a') as $a) {
			$text = self::recursive_array_search('#text', $a);
			$links[] = [
				'text' => $text[0],
				'href' => $a['href'],
			];
		}
		unset($dom);
		return $links;
	}
}