<?php

namespace parsing\wrappers;

class QueryPathParser
{
	public static function parseLinks($content, $contentType = 'text/html')
	{
		$links = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
		} else {
			$isXml = false;
		}

		$options = [
			'ignore_parser_warnings' => true,
			'use_parser' => $isXml ? 'xml' : 'html',
			'omit_xml_declaration' => true,
		//	'convert_to_encoding' => 'ISO-8859-1',
			'convert_from_encoding' => 'auto',
		//	'encoding' => 'UTF-8',	// when creating new document
		];

		if ($isXml) {
			// If document valid
			$elements = qp($content)->xpath('//offer', $options);
			foreach ($elements as $element) {
				$links[] = [
					'text' => $element->attr('id'),
					'href' => $element->children('url')->text(),
				];
			}
		} else {
			$elements = @qp($content, 'a' , $options);
			foreach ($elements as $element) {
				$links[] = [
					'text' => $element->text(),
					'href' => $element->attr('href'),
				];
			}
		}
		unset($elements);
		return $links;
	}
}