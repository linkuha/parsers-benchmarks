#!/usr/bin/env php
<?php
require_once '__consts.php';

cli_set_process_title('PHP parser - ' . basename(__FILE__));
ini_set('memory_limit', MAX_MEMORY_LIMIT);

//require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";
require_once dirname(dirname(__DIR__)) . "/autoload.php";

$file = $argv[1];
$type  = $argv[2];
$count = $argv[3];

if (!ctype_digit($count)) {
	file_put_contents( 'php://stderr', 'Wrong count value: '.$count);
	exit(1);
}

switch ($type) {
	case 'html':
		$contentType = 'text/html';
		break;
	case 'xhtml':
		$contentType = 'application/xhtml+xml';
		break;
	case 'xml':
		$contentType = 'text/xml';
		break;
	default:
		file_put_contents( 'php://stderr', 'Wrong type: '.$type);
		exit(1);
}

$url = str_replace(APP_ROOT, APP_ROOT_URL, $file);
$url = str_replace('\\', '/', $url);

$content = file_get_contents($url, false, null, 0, MAX_INPUT_SIZE);
if ( $content === false )
{
	file_put_contents( 'php://stderr', 'Unable to read file: '.$file );
	exit;
}

use helpers\TidyHelper;

$memAvailable = filter_var(ini_get("memory_limit"), FILTER_SANITIZE_NUMBER_INT);
$memAvailable = $memAvailable * 1024 * 1024;

$memStat = array(
	//"HIGHEST_MEMORY" => 0,
	"HIGHEST_DIFF" => 0,
	//"AVERAGE" => array(),
);

$memDiff = 0;
$memStart = memory_get_peak_usage(false);

$timeStart = microtime(true);

for ($i=0; $i < $count; $i++) {
	parseLinks($content, $contentType);

	$memUsed = memory_get_peak_usage(false);
	$memDiff = $memUsed - $memStart;

	//$memStat['HIGHEST_MEMORY'] = $memUsed > $memStat['HIGHEST_MEMORY'] ? $memUsed : $memStat['HIGHEST_MEMORY'];
	$memStat['HIGHEST_DIFF'] = $memDiff > $memStat['HIGHEST_DIFF'] ? $memDiff : $memStat['HIGHEST_DIFF'];
	//$memStat['AVERAGE'][] = $memDiff;
}
$elapsedTime = microtime(true) - $timeStart;

//$percentage = (($memStart + $memStat['HIGHEST_DIFF']) / $memAvailable) * 100;
//$memStat['AVERAGE'] = array_sum($memStat['AVERAGE']) / count($memStat['AVERAGE']);

echo $memStat['HIGHEST_DIFF'] . PHP_EOL;
echo $elapsedTime . PHP_EOL;



global $isXml;

	// Specifies if parse includes needle substring
	const EXCL = true;
	const INCL = false;

	// Specifies if parse returns the text before or after the needle substring
	const BEFORE = true;
	const AFTER = false;

function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8')
	{
		$links = array();
		global $isXml;

		if ($contentType === 'text/xml') {
			$isXml = true;
			$needleTag = "offer";
			$needleAttr = "id";
			$needleChild = "url";
		} else {
			$isXml = false;
			$needleTag = "a";
			$needleAttr = "href";
			$needleChild = "";
		}

		$harvest = getElementsByTagName($content, $needleTag);
		$count = count($harvest);

		for ($i=0; $i < $count; $i++) {
			$text = nodeValue($harvest[$i]);
			$attr = getAttribute($harvest[$i], $needleAttr);
			if ($isXml) {
				$links[] = [
					'text' => iconv($inputEncoding, "UTF-8", $attr),
					'href' => firstChild($text, $needleChild),
				];
			} else {
				$links[] = [
					'text' => iconv($inputEncoding, "UTF-8", $text),
					'href' => $attr,
				];
			}
		}
		unset($links);
	}

function nodeValue($node)
	{
		return substr($node, strpos($node, '>') + 1);
	}

function getElementsByTagName($fragment, $needleTag)
	{
		return tag($fragment, $needleTag);
	}

function firstChild($fragment, $needleTag)
	{
		$harvest = tag($fragment, $needleTag);
		return $harvest[0];
	}

function splitString($haystack, $needle, $direction, $includeNeedle)
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

function returnBetween($string, $start, $stop, $type)
	{
		$temp = splitString($string, $start, AFTER, $type);
		if (null === $temp) return null;
		return splitString($temp, $stop, BEFORE, $type);
	}

	// Single regex nevertheless had to use
function tag($content, $tagName)
	{
		preg_match_all('#<'.$tagName.'(.*)</'.$tagName.'>#siU', $content, $matches);
		return $matches[0];
	}

function getAttribute($tagString, $attribute)
	{
		global $isXml;

		// Use Tidy library to 'clean' input
		//if (!$isXml) {
		//	$tagString = TidyHelper::getClean($tagString);
		//}

		// Remove all line feeds from the string
		$tagString = str_replace("\r", "", $tagString);
		$tagString = str_replace("\n", "", $tagString);

		// find the properly quoted value for the attribute
		return returnBetween($tagString, strtolower($attribute)."=\"", "\"", EXCL);
	}
