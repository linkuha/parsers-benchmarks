<?php

namespace parsing\wrappers;

use helpers\DOMHelper;
use helpers\TidyHelper;

class SimpleXmlParser
{
	public static function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8')
	{
		if ($contentType === 'text/html') {
			$isXml = false;
		} else {
			$isXml = true;
		}
		$links = array();

		if ($isXml) {
			$dom = DOMHelper::loadErrorless($content, $isXml, false, $inputEncoding);
			if (!$dom) return null;
			$simplexml = simplexml_import_dom($dom);
		} else {
			//$xmlString = TidyHelper::getCleanNew($content, $inputEncoding, 'text/xml');
			//$xml = simplexml_load_string($xmlString);
			return null;
		}
		if ($simplexml === false)	{
			// If need to get errors, uncomment and edit code below
			// foreach(libxml_get_errors() as $error) {
			// 	echo "<br>", $error->message;
			// }
			return false;
		}
		if ($contentType === 'text/xml') {
			foreach ($simplexml->xpath('//offer') as $link) {
				//foreach ($xml->a as $link) {
				$links[] = [
					'text' => (string) $link->attributes()['id'],
					'href' => (string) $link->url,
				];
			}
		} else {
			foreach ($simplexml->xpath('//a') as $link) {
				//foreach ($xml->a as $link) {
				$links[] = [
					'text' => (string) $link,
					'href' => (string) $link->attributes()['href'],
				];
			}
		}

		unset($simplexml);
		return $links;
	}


	// SimpleXMLElement
}