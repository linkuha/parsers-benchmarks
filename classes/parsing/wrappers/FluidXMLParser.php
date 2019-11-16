<?php

namespace parsing\wrappers;

use \FluidXml\FluidXml;
use \FluidXml\FluidNamespace;
use helpers\DOMHelper;

class FluidXMLParser
{
	public static function parseLinks($xml)
	{
		$links = array();

		//$dom = DOMHelper::loadErrorless($xml, false, false);

		$options = [
		//	'root'       => 'doc',		// The root node of the document.
			'version'    => '1.0',		// The version for the XML header.
			'encoding'   => 'UTF-8',	// The encoding for the XML header.
		//	'stylesheet' => null		// An url pointing to an XSL file.
		];

		$doc = new FluidXml('html');
		$doc->add($xml); 	// XML/XHTML string or DOMDocument or SimpleXMLElement

		foreach($doc->query('//a') as $a) {
			$links[] = [
				'text' => $a->nodeValue,
				'href' => $a->getAttribute('href'),
			];
		}
		unset($doc);
		return $links;
	}
}