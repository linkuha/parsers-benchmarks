<?php

namespace parsing\wrappers;

use helpers\DOMHelper;

/**
 * Class DomParser
 *
 * often overlooked thing:
 *
 * <title>The Title</title>
 * is not one, but two nodes. A DOMElement with a DOMText child. Likewise, this:
 *
 * <div class="header">
 * is really three nodes: the DOMElement with a DOMAttr holding a DOMText.
 * Because all these inherit their properties and methods from DOMNode
 *
 *
 * @package parsing
 */
class DomParser
{
	public static function parseLinks($content, $contentType = 'text/html', $notXpath = false)
	{
		if ($contentType === 'text/xml') {
			$isXml = true;
		} else {
			$isXml = false;
		}
		$harvest_links = array();
		$dom = DOMHelper::loadErrorless($content, $isXml, false, 'UTF-8');
		if (!$dom) return null;

		if ($notXpath) {
			if ($isXml) {
				foreach ($dom->getElementsByTagName("offer") as $a)	{
					$harvest_links[] = [
						'text' => $a->getAttribute("id"),
						'href' => $a->firstChild->textContent,
					];
				}
			} else {
				// так же возможны итерации по childNodes
				foreach ($dom->getElementsByTagName("a") as $a)	{
					$harvest_links[] = [
						'text' => $a->textContent,
						'href' => $a->getAttribute("href"),
					];
				}
			}

		} else {
			$xpath = new \DOMXPath($dom);

			if ($isXml) {
				foreach($xpath->query('//offer') as $a) {
					$harvest_links[] = [
						'text' => $a->getAttribute("id"),
						'href' => $a->firstChild->textContent,
					];
				}
			} else {
				foreach($xpath->query('//a') as $a) {
					$harvest_links[] = [
						'text' => $a->textContent,
						'href' => $a->getAttribute("href"),
					];
				}
			}

			// The code below is better and faster of course if we need avoid empty href's,
			// but as part of the problem, we do not reject the elements without href
			/*
			foreach($xpath->query('//a/@href') as $href) {
				$links[] = [
					'text' => $href->parentNode->nodeValue,
					'href' => $href->nodeValue,
				];
			}*/
		}
		unset($dom, $xpath);
		return $harvest_links;
	}
}