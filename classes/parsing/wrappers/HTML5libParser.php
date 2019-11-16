<?php

namespace parsing\wrappers;

use Masterminds\HTML5;

class HTML5libParser
{
	public static function parseLinks($html, $inputEncoding = 'UTF-8')
	{
		$links = array();

		$html5 = new HTML5(array(
			// http://stackoverflow.com/questions/25484217/xpath-with-html5lib-in-php
			'disable_html_ns' => true,
		));
		$stream = new HTML5\Parser\StringInputStream($html, $inputEncoding);
		$dom = $html5->parse($stream);
		$xpath = new \DOMXPath($dom);
		$elements = $xpath->query('//a');

		foreach ($elements as $element) {
			$links[] = [
				'text' => $element->textContent,
				'href' => $element->getAttribute("href"),
			];
		}
		unset($html5);
		unset($dom);

		return $links;
	}

	public static function parseLinks2($fileOrUrl)
	{
		$links = array();

		$html5 = new HTML5(array(
			// http://stackoverflow.com/questions/25484217/xpath-with-html5lib-in-php
			'disable_html_ns' => true,
		));
		$handle = fopen($fileOrUrl, 'r');
		if ($handle) {
			$dom = $html5->load($handle);
			$xpath = new \DOMXPath($dom);
			$elements = $xpath->query('//a');

			foreach ($elements as $element) {
				$links[] = [
					'text' => $element->textContent,
					'href' => $element->getAttribute("href"),
				];
			}

		}
		unset($html5);
		unset($dom);
	}
}