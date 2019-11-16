#!/usr/bin/env php
<?php
require_once '__consts.php';

cli_set_process_title('PHP parser - ' . basename(__FILE__));

//require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";
require_once dirname(dirname(__DIR__)) . "/autoload.php";

$file = $argv[1];
$type  = $argv[2];
$count = $argv[3];

if (!ctype_digit($count)) {
	file_put_contents( 'php://stderr', 'Wrong count value: '.$count);
	exit(1);
}
if ($count > 100) {
	echo 0 . PHP_EOL;
	echo 0 . PHP_EOL;
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

use helpers\JsonEncodingWrapper;

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
	$res = parseLinksJS($url, $contentType);
	unset($res);

	$memUsed = memory_get_peak_usage(false);
	$memDiff = $memUsed - $memStart;

	//$memStat['HIGHEST_MEMORY'] = $memUsed > $memStat['HIGHEST_MEMORY'] ? $memUsed : $memStat['HIGHEST_MEMORY'];
	$memStat['HIGHEST_DIFF'] = $memDiff > $memStat['HIGHEST_DIFF'] ? $memDiff : $memStat['HIGHEST_DIFF'];
	//$memStat['AVERAGE'][] = $memDiff;

	if ($count > 1) {
		usleep(10000);
	}
}
usleep(10000);
$elapsedTime = microtime(true) - $timeStart;

//$percentage = (($memStart + $memStat['HIGHEST_DIFF']) / $memAvailable) * 100;
//$memStat['AVERAGE'] = array_sum($memStat['AVERAGE']) / count($memStat['AVERAGE']);

echo $memStat['HIGHEST_DIFF'] . PHP_EOL;
echo $elapsedTime . PHP_EOL;


function parseLinksJS($url, $contentType = 'text/html') {

		if ($contentType === 'text/xml') {
			$test = 'XML';
		} else {
			$test = 'HTML';
		}

		$DS 			=	DIRECTORY_SEPARATOR;
		$progPath 		= dirname(dirname(__DIR__)) . "{$DS}bin{$DS}phantomjs";
		$scriptPath		=	dirname(dirname(__DIR__)) . "{$DS}resources{$DS}phantomjs";
		$fullCommand	=	$progPath . " {$scriptPath}{$DS}test{$test}.js {$url}";

		$res = shell_exec(escapeshellcmd($fullCommand));
		//@flush();	//
		if ($res) {
			try {
				$json = JsonEncodingWrapper::json_decode($res, true);
				return $json;
			} catch (\Exception $e) {
				echo $e->getMessage() . PHP_EOL;
				echo $e->getTraceAsString() . PHP_EOL;
				echo $res . PHP_EOL;
				return null;
			}
		} else {
			return null;
		}
	}
