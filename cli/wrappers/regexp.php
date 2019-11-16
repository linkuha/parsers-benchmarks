#!/usr/bin/env php
<?php
require_once '__consts.php';

cli_set_process_title('PHP parser - ' . basename(__FILE__));
ini_set('memory_limit', MAX_MEMORY_LIMIT);

//require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";
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


$memAvailable = filter_var(ini_get("memory_limit"), FILTER_SANITIZE_NUMBER_INT);
$memAvailable = $memAvailable * 1024 * 1024;

$memStat = array(
	"HIGHEST_MEMORY" => 0,
	"HIGHEST_DIFF" => 0,
	"AVERAGE" => array(),
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


function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8')
	{
		if (!function_exists('preg_match_all')) {
			throw new \Exception('The PCRE library is not loaded or is not available.');
		}

		$links = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
			$content = preg_replace('#\\r\\n\s*#', '', $content);
			$reg1 = '#<offer.*?id="(.*?)".*?>(.*?)</offer>#';
		} else {
			$isXml = false;
			// Will find also commented out elements
			$reg1 = '#<a\s{1,}.*?href\s*=\s*[\'\\"](.*?)[\'\\"].*?>(.*?)</a#';
		}

		$reg2 = '#>(.*?)</#';

		preg_match_all($reg1, $content, $nodes, PREG_SET_ORDER);

		foreach($nodes as $a) {
			if ($isXml) {
				if(preg_match($reg2, $a[2], $text) === 1) $a[2] = $text[1];
				$links[] = [
					'href' => $a[2],
					'text' => iconv($inputEncoding, "UTF-8", $a[1]),
				];
			} else {
				if(preg_match($reg2, $a[2], $text) === 1) $a[2] = $text[1];
				$links[] = [
					'text' => iconv($inputEncoding, "UTF-8", $a[2]),
					'href' => $a[1],
				];
			}
		}
		unset($links);
	}
