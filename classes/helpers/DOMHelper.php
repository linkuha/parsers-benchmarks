<?php

namespace helpers;

class DOMHelper
{
	public static function loadErrorless(
		$content,
		$isXml = false,
		$convertToEntities = false,
		$inputEncoding = 'UTF-8')
	{
		$internalErrors = libxml_use_internal_errors(true);
		$disableEntities = libxml_disable_entity_loader(true);

		if (null === $inputEncoding) {
			$dom = new \DOMDocument('1.0');
		} else {
			$dom = new \DOMDocument('1.0', $inputEncoding);
		}

		$dom->strictErrorChecking = false;
		$dom->validateOnParse = true;

		if ($convertToEntities) {
			set_error_handler(function () {throw new \Exception();});
			try {
				// Convert charset to HTML-entities (russian - мнемоники)
				// to work around bugs in DOMDocument::loadHTML()
				$content = mb_convert_encoding($content, 'HTML-ENTITIES', $inputEncoding);
			} catch (\Exception $e) {
			}
			restore_error_handler();
		}
		$success = null;
		if ('' !== trim($content)) {
			if (!$isXml) {
				// See option LIBXML_COMPACT for boost performance?
				// See LIBXML_NOWARNING | LIBXML_NOERROR if needn't debug
				// @link http://php.net/manual/en/libxml.constants.php
				$success = @$dom->loadHTML($content);
			} else {
				// XML not ignores spaces and tabs, unlike HTML, so set option below FALSE
				$dom->preserveWhiteSpace = false;
				// See option LIBXML_NONET
				$success = @$dom->loadXML($content);
			}
		}

		// If need to get errors
		$errors = libxml_get_errors();
		if (!empty($errors)) {
			$errorsString = '';
			foreach(libxml_get_errors() as $key => $error) {
			 	$errorsString .= ($key+1) . ": " . $error->message . PHP_EOL . PHP_EOL;
			}
			$DS 			=	DIRECTORY_SEPARATOR;
			$tempDir 		= 	__DIR__.	"{$DS}..{$DS}..{$DS}runtime{$DS}log";
			$outputLogFile 	=	$tempDir .	"{$DS}dom-last-errors.log";

			$handle = fopen($outputLogFile, 'w+');
			if ($handle) {
				fwrite($handle, $errorsString);
				fclose($handle);
			}
			libxml_clear_errors();
		}

		libxml_use_internal_errors($internalErrors);
		libxml_disable_entity_loader($disableEntities);

		if (!$success) {
			return null;
			//throw new \Exception('Error parsing document.');
		}
		return $dom;
	}

}