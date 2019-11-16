#!/usr/bin/env php
<?php
require_once '__consts.php';

cli_set_process_title('PHP parser - ' . basename(__FILE__));
ini_set('memory_limit', MAX_MEMORY_LIMIT);

require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";
//require_once dirname(dirname(__DIR__)) . "/autoload.php";

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
use TheSeer\fDOM;

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



function parseLinks($html, $contentType = 'text/html')
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


		unset($dom);
		unset($links);
	}
