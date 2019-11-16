<?php

namespace parsing\wrappers;


class PhpQueryParser
{
	public static function parseLinks($content, $contentType = 'text/html')
	{
		$links = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
		} else {
			$isXml = false;
		}

		// With new DocumentXHTML - DOM don't loads. using autodetect of doctype.
		// default charset is ISO-8859-1
		$doc = \phpQuery::newDocument($content);

		if ($isXml) {
			foreach($doc->find('offer', DOMNODE) as $a) {
				$links[] = [
					'text' => $a->getAttribute("id"),
					'href' => $a->firstChild->textContent,
				];
			}
		} else {
			foreach($doc->find('a', DOMNODE) as $a) {
				$links[] = [
					'text' => $a->textContent,
					'href' => $a->getAttribute("href"),
				];
			}
		}
		unset($doc);
		return $links;
	}
}