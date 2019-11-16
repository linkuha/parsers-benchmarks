<?php

namespace parsing\wrappers;

use FluentDOM;

class FluentDomParser
{
	public static function parseLinks($content, $contentType = 'text/html', $css = false)
	{
		$harvest_links = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
		} else {
			$isXml = false;
		}

		$dom = new FluentDOM\Document();
		if (!$isXml) {
			@$dom->loadHTML($content);
		} else {
			@$dom->loadXML($content);
		}
		if ($css) {
			if ($isXml) {
				//$fd = FluentDOM::QueryCss($content, $contentType);
				foreach ($dom->querySelectorAll('offer') as $node) {
					$harvest_links[] = [
						'text' => $node->getAttribute("id"),
						//'href' => $node->firstElementChild()->textContent,
					];
				}
			} else {
				//$fd = FluentDOM::QueryCss($content, $contentType);
				foreach ($dom->querySelectorAll('a') as $node) {
					$harvest_links[] = [
						'text' => $node->textContent,
						'href' => $node->getAttribute("href"),
					];
				}
			}
		} else {
			if ($isXml) {
				//$fd = FluentDOM::Query($content, $contentType);
				$xpath = new FluentDOM\Xpath($dom);
				foreach ($xpath->evaluate('//offer') as $node) {
					$harvest_links[] = [
						'text' => $node->getAttribute("id"),
						//'href' => $node->firstElementChild()->textContent,
					];
				}
			} else {
				//$fd = FluentDOM::Query($content, $contentType);
				$xpath = new FluentDOM\Xpath($dom);
				foreach ($xpath->evaluate('//a') as $node) {
					$harvest_links[] = [
						'text' => $node->textContent,
						'href' => $node->getAttribute("href"),
					];
				}
			}
		}
		unset($dom);
		return $harvest_links;
	}
}