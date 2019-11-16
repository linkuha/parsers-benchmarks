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

use parsing\SimpleXMLReader;

class SimpleXMLReaderParser extends SimpleXMLReader
{
	protected $data = array();

	public function __construct()
	{
		// by node name or xpath (why don't work???)
		$this->registerCallback("offer", array($this, "callbackLinks"));
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	protected function callbackLinks($reader)
	{
		$xml = $reader->expandSimpleXml();

		// with namespace
		//$attributes = $xml->attributes('rdf', true);
		$attributes = $xml->attributes();
		$id = (string) $attributes->{'id'};
		$url = $xml->xpath('/url');

		$this->data[] = [
			'text' => (string) $id,
			'href' => (string) $url->title,
		];
		return true;
	}
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
	$reader = new SimpleXMLReaderParser();
	//не работает, если вернулся 404 код с телом страницы
	$reader->open((string) $url);
	$reader->parse();
	$reader->close();

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


//header ("Content-type: text/html, charset=utf-8;");


