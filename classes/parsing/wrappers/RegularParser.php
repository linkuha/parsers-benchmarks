<?php

namespace parsing\wrappers;

class RegularParser
{
	public static function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8')
	{
		if (!function_exists('preg_match_all')) {
			throw new \Exception('The PCRE library is not loaded or is not available.');
		}

		$links = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
			$content = preg_replace('#\\r\\n\s*#', '', $content);
			$reg1 = '#<offer.*?id="(.*?)".*?>(.*?)</offer>#';
		} else {
			$isXml = false;
			// Will find also commented out elements
			$reg1 = '#<a\s{1,}.*?href\s*=\s*[\'\\"](.*?)[\'\\"].*?>(.*?)</a#';
		}

		$reg2 = '#>(.*?)</#';

		preg_match_all($reg1, $content, $nodes, PREG_SET_ORDER);

		foreach($nodes as $a) {
			if ($isXml) {
				if(preg_match($reg2, $a[2], $text) === 1) $a[2] = $text[1];
				$links[] = [
					'href' => $a[2],
					'text' => iconv($inputEncoding, "UTF-8", $a[1]),
				];
			} else {
				if(preg_match($reg2, $a[2], $text) === 1) $a[2] = $text[1];
				$links[] = [
					'text' => iconv($inputEncoding, "UTF-8", $a[2]),
					'href' => $a[1],
				];
			}
		}
		return $links;
	}
}