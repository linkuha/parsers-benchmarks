<?php

namespace parsing\wrappers;

use helpers\TidyHelper;
use Sabre\Xml;

class SabreXMLParser
{
	public static function parseLinks($xml)
	{
		$links = array();

		$options = [
			//	'root'       => 'doc',		// The root node of the document.
			'version'    => '1.0',		// The version for the XML header.
			'encoding'   => 'UTF-8',	// The encoding for the XML header.
			//	'stylesheet' => null		// An url pointing to an XSL file.
		];

		$xml = TidyHelper::getCleanNew($xml);

		$service = new Xml\Service();


		$result = $service->parse($xml);

		return $links;
	}
}