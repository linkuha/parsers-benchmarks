<?php

namespace parsing\wrappers;

use TheSeer\fDOM;

class FDomDocumentParser
{
	public static function parseLinks($html, $contentType = 'text/html', $css = false)
	{
		$links = array();

		// fDOMDocument extend the standard DOM to use exceptions
		// at all occasions of errors instead of PHP warnings or notices.
		// note that sometimes learn where is error occured is helpful and
		// may be performance tests unnecessary here
		$dom = new fDOM\fDOMDocument();

		if ($contentType === 'text/xml') {
			$isXml = true;
		} else {
			$isXml = false;
		}
		set_error_handler(function () {throw new \Exception(); });
		try {
			if ($isXml) {
				$dom->loadXML($html);
			} else {
				$dom->loadHTML($html);
			}
		} catch (\Exception $e) {

		}
		restore_error_handler();

		if ($css) {
			if ($isXml) {
				foreach($dom->select('offer') as $node) {
					$links[] = [
						'text' => $node->getAttribute("id"),
						'href' => $node->getChildrenByTagName('url')->textContent,
					];
				}
			} else {
				foreach($dom->select('a') as $node) {
					$links[] = [
						'text' => $node->textContent,
						'href' => $node->getAttribute("href"),
					];
				}
			}
		} else {
			if ($isXml) {
				foreach($dom->query('//offer') as $node) {
					$links[] = [
						'text' => $node->getAttribute("id"),
						'href' => $node->getChildrenByTagName('url')->textContent,
					];
				}
			} else {
				foreach($dom->query('//a') as $node) {
					$links[] = [
						'text' => $node->textContent,
						'href' => $node->getAttribute("href"),
					];
				}
			}
		}

		unset($dom);
		return $links;
	}
}