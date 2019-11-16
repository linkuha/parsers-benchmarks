<?php

namespace parsing\wrappers;

use helpers\TidyHelper;

class StringParser
{
	private static $isXml;

	// Specifies if parse includes needle substring
	const EXCL = true;
	const INCL = false;

	// Specifies if parse returns the text before or after the needle substring
	const BEFORE = true;
	const AFTER = false;

	public static function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8')
	{
		$links = array();

		if ($contentType === 'text/xml') {
			self::$isXml = true;
			$needleTag = "offer";
			$needleAttr = "id";
			$needleChild = "url";
		} else {
			self::$isXml = false;
			$needleTag = "a";
			$needleAttr = "href";
			$needleChild = "";
		}

		$harvest = self::getElementsByTagName($content, $needleTag);
		$count = count($harvest);

		for ($i=0; $i < $count; $i++) {
			$text = self::nodeValue($harvest[$i]);
			$attr = self::getAttribute($harvest[$i], $needleAttr);
			if (self::$isXml) {
				$links[] = [
					'text' => iconv($inputEncoding, "UTF-8", $attr),
					'href' => self::firstChild($text, $needleChild),
				];
			} else {
				$links[] = [
					'text' => iconv($inputEncoding, "UTF-8", $text),
					'href' => $attr,
				];
			}
		}
		return $links;
	}

	private static function nodeValue($node)
	{
		return substr($node, strpos($node, '>') + 1);
	}

	private static function getElementsByTagName($fragment, $needleTag)
	{
		return self::tag($fragment, $needleTag);
	}

	private static function firstChild($fragment, $needleTag)
	{
		$harvest = self::tag($fragment, $needleTag);
		return $harvest[0];
	}

	private static function splitString($haystack, $needle, $direction, $includeNeedle)
	{
		// Case insensitive parse, convert string and needle substring to lower case
		$lowerString = strtolower($haystack);
		$marker = strtolower($needle);

		// Return text BEFORE the needle
		if($direction == BEFORE)
		{
			if ($includeNeedle == EXCL) {
				$splitPos = strpos($lowerString, $marker);
				if (false === $splitPos) return null;
			}
			else {
				$splitPos = strpos($lowerString, $marker);
				if (false === $splitPos) return null;
				$splitPos += strlen($marker);

			}
			$parsedString = substr($haystack, 0, $splitPos);
		}
		// Return text AFTER the needle
		else
		{
			if ($includeNeedle == EXCL) {
				$splitPos = strpos($lowerString, $marker);
				if (false === $splitPos) return null;
				$splitPos += strlen($marker);
			}
			else {
				$splitPos = strpos($lowerString, $marker);
				if (false === $splitPos) return null;
			}
			$parsedString =  substr($haystack, $splitPos, strlen($haystack));
		}
		return $parsedString;
	}

	private static function returnBetween($string, $start, $stop, $type)
	{
		$temp = self::splitString($string, $start, AFTER, $type);
		if (null === $temp) return null;
		return self::splitString($temp, $stop, BEFORE, $type);
	}

	// Single regex nevertheless had to use
	private static function tag($content, $tagName)
	{
		preg_match_all('#<'.$tagName.'(.*)</'.$tagName.'>#siU', $content, $matches);
		return $matches[0];
	}

	private static function getAttribute($tagString, $attribute)
	{
		// Use Tidy library to 'clean' input
		//if (!self::$isXml) {
		//	$tagString = TidyHelper::getClean($tagString);
		//}

		// Remove all line feeds from the string
		$tagString = str_replace("\r", "", $tagString);
		$tagString = str_replace("\n", "", $tagString);

		// find the properly quoted value for the attribute
		return self::returnBetween($tagString, strtolower($attribute)."=\"", "\"", EXCL);
	}

}