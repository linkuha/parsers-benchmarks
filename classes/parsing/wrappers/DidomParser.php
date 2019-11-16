<?php

namespace parsing\wrappers;

use DiDom;

class DidomParser
{
	public static function parseLinks($html, $contentType = 'text/html', $inputEncoding = 'UTF-8', $css = false)
	{
		if ($contentType === 'text/xml') {
			$isXml = true;
			$type = 'xml';
		} else {
			$isXml = false;
			$type = 'html';
		}

		// Default encoding is UTF-8
		$dom = new DiDom\Document($html, false, $inputEncoding, $type);
		$links = array();

		if ($css) {
			if ($isXml) {
				foreach($dom->find('offer', DiDom\Query::TYPE_CSS, false) as $a) {
					$links[] = [
						'text' => $a->getAttribute('id'),
						'href' => $a->firstChild->textContent,
					];
				}
			} else {
				foreach($dom->find("a") as $a) {
					$links[] = [
						'text' => $a->text(),		// text() = textValue, html()
						'href' => $a->getAttribute('href'),
					];
				}
			}
		} else {
			if ($isXml) {
				foreach($dom->find('//offer', DiDom\Query::TYPE_XPATH, false) as $a) {
					$links[] = [
						'text' => $a->getAttribute('id'),
						'href' => $a->firstChild->textContent,
					];
				}
			} else {
				foreach($dom->find("//a", DiDom\Query::TYPE_XPATH) as $a) {
					$links[] = [
						'text' => $a->text(),		// text() = textValue, html() - nodeValue
						'href' => $a->getAttribute('href'),
					];
				}
			}
		}
		unset($dom);
		return $links;
	}

}