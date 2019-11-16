<?php

namespace parsing\wrappers;

use helpers\TidyHelper;
use helpers\DOMHelper;

class XMLReaderParser
{
	public static function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8')
	{
		$links = array();
		$reader = new \XMLReader;

		//$dom = DOMHelper::loadHtmlErrorless($html, $inputEncoding);
		//$xmlString = $dom->saveXML($dom->documentElement);

		if ($contentType !== 'text/xml') {
			$xmlString = TidyHelper::getCleanHtml5($content, $inputEncoding, 'text/xml');
		} else {
			return null;
		}
		$reader->XML($xmlString);

		while($reader->read() !== FALSE) {
			if($reader->name === 'a' && $reader->nodeType === \XMLReader::ELEMENT) {
				$href = $reader->getAttribute('href');
				$links[] = [
					'href' => $href,
					'text' => $reader->readString(),
				];
			}
		}
		unset($reader);
		return $links;
	}

	public static function parseLinksStream($file)
	{
		$links = array();
		$reader = new \XMLReader;
		if (!$reader) return null;

		$reader->open($file);
		while($reader->read() !== FALSE) {
			if($reader->name === 'offer' && $reader->nodeType === \XMLReader::ELEMENT) {
				$id = $reader->getAttribute('id');
				$url = "";
				while($reader->read() !== FALSE) {
					if ($reader->name === 'offer' && $reader->nodeType === \XMLReader::END_ELEMENT) {
						break;
					}
					if($reader->name === 'url' && $reader->nodeType === \XMLReader::ELEMENT) {
						$url = $reader->readString();
					}
				}
				$links[] = [
					'href' => $url,
					'text' => $id,
				];
			}
		}
		$reader->close();
		unset($reader);
		return $links;
	}

}