<?php

namespace parsing\wrappers;

use parsing\SimpleHTMLDom;

class SimpleHtmlParser
{
	public static function parseLinks($html)
	{
		// Detect encoding by document <meta>, if charset not specified:
		// default: ISO-8859-1 (rely to standard server configuration probably)
		// second detect by mb_detect_encoding(), and if not success - says 'UTF-8'
		$dom = SimpleHTMLDom::str_get_html($html);
		if (!$dom) return null;

		$links = array();
		foreach($dom->find('a') as $a) {
			$links[] = [
				'text' => $a->text(),
				'href' => $a->href,
			];
		}
		unset($dom);
		return $links;
	}
}