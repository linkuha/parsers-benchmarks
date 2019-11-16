<?php

namespace parsing\wrappers;

require_once (dirname(__DIR__).'/Ganon.php');

class GanonParser
{
	public static function parseLinks($html, $inputEncoding = 'UTF-8')
	{
		$links = array();

		$doc = str_get_dom($html);

		foreach($doc('a') as $node) {
			$links[] = [
				'text' => iconv($inputEncoding, "UTF-8", $node->getPlainText()),
				'href' => $node->getAttribute("href"),
			];
		}
		unset($doc);
		return $links;
	}
}