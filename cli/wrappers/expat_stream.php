#!/usr/bin/env php
<?php
require_once '__consts.php';

cli_set_process_title('PHP parser - ' . basename(__FILE__));

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
	$reader = new ExpatParser();
	$reader->parseLinksStream((string) $url, 1);

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


class Offer {

	private $id;
	private $href;

	public function setId($id) {
		$this->id .= $id;
	}

	public function setHref($href) {
		$this->href = $href;
	}

	public function getId() {
		return $this->id;
	}

	public function getHref() {
		return $this->href;
	}
}

/** The parser can't be configured to automatically call individual methods
 * for each specific tag; instead, you must handle this yourself.
*/
class ExpatParser
{
	const FUNC_FOPEN 	= 1;
	const FUNC_FILE		= 2;

	private $tag;
	private $item;

	private $data = array();

	public function getData() {
		return $this->data;
	}

	private function offerStart($parser, $tag, $attributes) {
		if ('offer' == $tag) {
			$this->item = new Offer();
			$this->item->setId($attributes['id']);
		} elseif ('url' == $tag) {
			$this->tag = $tag;
		}
	}

	private function offerData($parser, $data) {
		if ('url' == $this->tag && !empty($this->item)) {
			$this->item->setHref($data);
		}
	}

	private function offerEnd($parser, $tag) {
		if ('offer' == $tag) {
			$this->data[] = [
				'text' => $this->item->getId(),
				'href' => $this->item->getHref(),
			];
			unset($this->item);
		}
	}

	private function stringElement($parser, $str) {
		//todo ??
	}

	/**
	 * @param      $fileOrUrl
	 * @param int  $func
	 * @param bool $everychar
	 * @return array
	 * @throws \Exception
	 * @internal param string $file_or_url
	 * @internal param bool $funcFopen
	 */
	public function parseLinksStream($fileOrUrl, $func = self::FUNC_FOPEN, $everychar = false) {
		// 1 parameter: The supported output encodings are ISO-8859-1, UTF-8 and US-ASCII.
		// eq. option XML_OPTION_TARGET_ENCODING
		// default: UTF-8 since php 5.0.2
		$parser = xml_parser_create();

		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'offerStart', 'offerEnd');
		xml_set_character_data_handler($parser, 'offerData');
		// bring to a unified register
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, true);

		if (self::FUNC_FOPEN === $func) {
			$handle = fopen($fileOrUrl, 'r');
			if ($handle === false) {
				throw new \Exception("fopen() can't read file (ExpatParser)");
			}
			if ($everychar === false) {
				while ($data = fread($handle, 4096)) {
					if (!xml_parse($parser, $data, feof($handle))) {
						throw new \Exception(
							"XML Error: ".
							xml_error_string(xml_get_error_code($parser)).
							" at line ".xml_get_current_line_number($parser)
						);
					}
					//usleep(1000);
				}
			} else {
				$firstStep = true;
				$data = "";
				while (!feof($handle)) {
					$char = fgetc($handle);
					$data .= $char;
					if ($char != '>') { continue; }
					if ($firstStep) {
						$data = strstr($data, '<?');
						$firstStep = false;
					}
					if (!xml_parse($parser, $data, feof($handle))) {
						throw new \Exception(
							"XML Error: ".
							xml_error_string(xml_get_error_code($parser)).
							" at line ".xml_get_current_line_number($parser)
						);
					}
					$data = "";
					//usleep(1000);
				}
			}
			fclose($handle);
		}
		if (self::FUNC_FILE === $func) {
			$lines = file($fileOrUrl);
			if ($lines === false) {
				throw new \Exception("file() can't read file (ExpatParser)");
			}
			foreach ($lines as $line_num => $line) {
				if (!xml_parse($parser, rtrim($line, '\n'))) {
					throw new \Exception(
						"XML Error: ".
						xml_error_string(xml_get_error_code($parser)).
						" at line ".xml_get_current_line_number($parser)
					);
				}
				//usleep(1000);
			}
		}
		xml_parser_free($parser);
		return $this->getData();
	}
}