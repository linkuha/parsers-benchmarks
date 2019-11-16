<?php

namespace parsing\wrappers;

use Symfony\Component\DomCrawler\Crawler;

class SymfonyParser
{
	public static function parseLinks($html, $contentType = 'text/html', $css = false)
	{
		// Find encoding in meta tag
		// default: ISO-8859-1 if not founded
		// Creates DOMDocument of version 1.0 and founded encoding

		$dom = new Crawler($html);
		$links = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
		} else {
			$isXml = false;
		}

		// специально показательно использую два варианта перебора
		// станд. foreach и each компонента (2-ой на каплю больше требует памяти)
		if (!$css) {
			if ($isXml) {
				foreach($dom->filterXPath('//offer') as $a) {
					$links[] = [
						'text' => $a->getAttribute('id'),
						'href' => $a->firstChild->textContent,  // text() = nodeValue
					];
				}
			} else {
				foreach($dom->filterXPath('//a') as $a) {
					$links[] = [
						'text' => $a->textContent,
						'href' => $a->getAttribute('href'),
					];
				}
			}
		} else {
			if ($isXml) {
				$links = $dom->filter('offer')
					->each(function ($node) {
						return $links[] = [
							'text' => $node->attr('id'),
							'href' => $node->children()->first()->text(),
						];
					});
			} else {
				$links = $dom->filter('a')
					->each(function ($node) {
						return $links[] = [
							'text' => $node->text(),
							'href' => $node->attr('href'),
						];
					});
			}
		}
		unset($dom);
		return $links;
	}

}