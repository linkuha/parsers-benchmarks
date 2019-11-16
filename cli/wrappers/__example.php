#!/usr/bin/env php
<?php
set_time_limit(0);
if (PHP_SAPI != "cli") {
	exit(1);
}
cli_set_process_title('Web documents analysing');

require_once '__consts.php';
ini_set('memory_limit', MAX_MEMORY_LIMIT);

require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";
require_once dirname(dirname(__DIR__)) . "/autoload.php";


if($argc != 4) {
	echo "Usage: ".$argv[0]." <file> <type: html|xhtml|xml> <iterations>\n";
	exit(1);
}

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
if ( $html === false )
{
	file_put_contents( 'php://stderr', 'Unable to read file: '.$file );
	exit;
}


$timeStart = microtime(true);

for ($i=0; $i < $count; $i++) {
	parseLinks($content, $contentType, $css = false);
}

$elapsedTime = microtime(true) - $timeStart;
echo "$elapsedTime\n";
