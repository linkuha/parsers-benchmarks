<?php

namespace parsing\wrappers;

require_once (dirname(__DIR__).'/AdvancedHtmlDom.php');

class AdvancedHtmlParser
{
	public static function parseLinks($html, $contentType = 'text/html', $css = false)
	{
		$links = array();

		// Creates DOMDocument without parameters
		if ($contentType === 'text/xml') {
			$isXml = true;
			$dom = str_get_xml($html);
		} else {
			$isXml = false;
			$dom = str_get_html($html);
		}

		if ($css) {
			if ($isXml) {
				foreach($dom->find('offer') as $a) {
					$links[] = [
						'text' => $a->id,	// text() = nodeValue (not textContent)
						'href' => $a->firstChild()->text(),
					];
				}
			} else {
				foreach($dom->find('a') as $a) {
					$links[] = [
						'text' => $a->text(),	// text() = nodeValue (not textContent)
						'href' => $a->href,
					];
				}
			}
		} else {
			if ($isXml) {
				foreach($dom->find('//offer') as $a) {
					$links[] = [
						'text' => $a->id,	// text() = nodeValue (not textContent)
						'href' => $a->firstChild()->text(),
					];
				}
			} else {
				foreach($dom->find('//a') as $a) {
					$links[] = [
						'text' => $a->text(),	// text() = nodeValue (not textContent)
						'href' => $a->href,
					];
				}
			}

		}
		unset($dom);
		return $links;
	}
}