#!/usr/bin/env php
<?php
set_time_limit(0);
if (PHP_SAPI != "cli") {
	exit(1);
}
cli_set_process_title('Web documents analysing');

require_once 'vendor/autoload.php';
require_once "autoload_test.php";


if($argc != 3) {
	echo "Usage: ".$argv[0]." <type: html|xhtml|xml> <iterations>\n";
	exit(1);
}

$type  = $argv[1];
$count = $argv[2];

if (!ctype_digit($count)) {
	file_put_contents( 'php://stderr', 'Wrong count value: '.$count);
	exit(1);
}

function getOS() {
	$uname = strtolower(php_uname());
	if (strpos($uname, "darwin") !== false) { return 'macosx'; }
	elseif (strpos($uname, "win") !== false) { return 'windows'; }
	elseif (strpos($uname, "linux") !== false) { return 'linux'; }
	else { return 'unknown'; }
}
if ('windows' !== getOS()) {
	shell_exec('chcp 65001');
}

// CSV values separator
$VS = ';';
$DS = DIRECTORY_SEPARATOR;
$docType = 8;
$iterator = new FilesystemIterator(__DIR__ . "{$DS}resources{$DS}test-docs");
switch ($type) {
	case 'html':
		$docType = 1;
		break;
	case 'xhtml':
		$docType = 2;
		break;
	case 'xml':
		$docType = 4;
		break;
	default:
		file_put_contents( 'php://stderr', 'Wrong type: '.$type);
		exit(1);
}
$filter = new RegexIterator($iterator, "/test_.*\\.{$type}$/");

$filelist = array();
foreach($filter as $entry) {
	$filelist[] = [
		'name' => $entry->getFilename(),
		'path' => $entry->getPathname(),
	];
}

function getFilesArray($filelist)
{
	$appRoot = __DIR__;
	$appRootUrl= "http://seo.local";
	$new = array();

	foreach ($filelist as $file) {
		$url = str_replace($appRoot, $appRootUrl, $file['path']);
		$url = str_replace('\\', '/', $url);
		//$name = substr($url, strrpos($url, DIRECTORY_SEPARATOR));
		//$name = substr($name, (0 - strpos($url, '.')));
		$new[] = [
			'path' => $file['path'],
			'url' => $url,
			'name' => $file['name']
		];
	}
	return $new;
}

function printProgress($all, $curr, $type, $str)
{
	$LC = "\033[K";
	$percent = intval(($curr/$all)*100);
	// Return to the beginning of the line and erase to the end of the line
	printf("{$LC}%3d%%... %s :: %s\r", $percent, strtoupper($type), $str);
}

$methods = AnalyzerOptions::get();
$all = count($methods);

$go = new Analyzer();
$go->initialize();
$go->setIterations((int) $count);

$outputDir 		= __DIR__ . "cli{$DS}results{$DS}handler";

foreach (getFilesArray($filelist) as $file) {
	$curr = 0;
	$outputFile 	=	$outputDir. "{$DS}{$file['name']}__{$count}.csv";
	$go->setBaseUri($file['url']);
	$content = "";
	if ($method !== AnalyzerOptions::PARSE_WITH_SAX_STREAM &&
		$method !== AnalyzerOptions::PARSE_WITH_XMLREADER_STREAM &&
		$method !== AnalyzerOptions::PARSE_WITH_SIMPLE_XMLREADER &&
		$method !== AnalyzerOptions::PARSE_WITH_PHANTOMJS) {
		$content = $go->getContent(Analyzer::CLIENT_GUZZLE, $file['url']);
		if ($content === null) {
			file_put_contents( 'php://stderr', 'Unable to get content: '.$file );
			exit(1);
		}
	}
	$handle = fopen($outputFile, 'w+');
	if (!$handle) {
		throw new \Exception("Can't create or open a file, or file open in other app!");
	}
	$res = "";
	foreach ($methods as $key => $method) {
		printProgress($all, $curr, $type, $method['name'] . " ({$file['name']})");
		$curr++;
		$result = null;

		//set_error_handler(function () {});
		try {
			$result = $go->testParser($content, $key, $docType);
			if (null !== $result) {
				$res .= '"' . $result['name'] . '"' . $VS . $result['memory'] . $VS . $result['time'] . $VS . count($result['links']) . PHP_EOL;
			}
		} catch (\Exception $ex) {

		}
		//restore_error_handler();
	}
	fwrite($handle, "\xEF\xBB\xBF".$res);
	fclose($handle);
	$LC = "\033[K";
	echo "{$LC}step done...\r";
	usleep(10000);
}
