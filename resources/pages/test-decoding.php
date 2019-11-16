<?php
//ini_set('display_errors', 0);
ob_start();

require_once "../../vendor/autoload.php";
require_once "../../autoload_test.php";

use Symfony\Component\Finder\Finder;

global $dir;
global $appRoot;
global $appRootUrl;

$dir = dirname(__DIR__) . '/encoded-docs';
$appRoot = dirname(dirname(__DIR__));
if (!is_dir($dir)) {
	exit(1);
}
$appRootUrl= "http://" . $_SERVER['HTTP_HOST'];

$cachePage = 'page-decoding.html';

/////////////////////////////////////////////////
// Get files list from the directory
/////////////////////////////////////////////////
// Variant 1

$finder = new Finder();
$iterator = $finder->files()->depth('< 2')->name('/\.(x?html|xml)$/')->in($dir);
$filelist = array();
foreach ($iterator as $file) {
	/**
	 * @var \SplFileInfo $file
	 */
	$filelist[] = $file->getRealPath();
}

/////////////////////////////////////////////////
// Variant 2

/*
$iterator2 = new \RecursiveIteratorIterator(
	new \RecursiveDirectoryIterator($dir),
	\RecursiveIteratorIterator::SELF_FIRST
);
$filter = new RegexIterator($iterator2, '/^.+\.html$/');
$filelist2 = array();
foreach ($filter as $entry) {
	$filelist2[] = $entry->getPathname();
}
*/

/////////////////////////////////////////////////
// Variant 3

//$iterator3 = new GlobIterator($dir .'/*/*\.html');
/*
$filelist3 = array();
foreach ($iterator3 as $entry) {
	$filelist3[] = $entry->getPathname();
}
*/

//var_dump($filelistNew);
//var_dump($filelist2);
//var_dump($filelist3);

global $dts;
global $encodings;

function getFilesArray($filelist)
{
	$new = array();
	global $appRoot;
	global $appRootUrl;
	global $dts;
	global $encodings;
	foreach ($filelist as $file) {
		$url = str_replace($appRoot, $appRootUrl, $file);
		$url = str_replace('\\', '/', $url);

		$dt = explode('.', substr($url, strrpos($url, '/') + 1))[0];

		$encoding = substr($url, 0, strrpos($url, '/'));
		$encoding = substr($encoding, strrpos($encoding, '/') + 1);

		$encodings[] = $encoding;
		$dts[] = $dt;

		$new[] = [
			'path' => $file,
			'url' => $url,
			'dt' => $dt,
			'encoding' => $encoding,
		];
	}
	return $new;
}
$filelistNew = getFilesArray($filelist);

$dts = array_unique($dts);
$encodings = array_unique($encodings);

sort($dts);
sort($encodings);

//var_dump($dts);
//var_dump($encodings);

// Encodings
// http://www.w3.org/TR/encoding/#encodings
// http://www.w3.org/TR/REC-xml/#NT-EncName

$go = new Analyzer();
$go->initialize();

function getDocType($dt) {
	switch ($dt) {
		case 'html4':
		case 'html5':
			return 1;
		case 'xhtml':
			return 2;
		case 'xml':
			return 4;
	}
}

$html = '<div style="position:fixed;background:white;border:1px solid black;">'.
	'<a href="'.$appRootUrl.'">MAIN</a> | <a href="test-decoding.php">Parse again!</a><br /><a href="#top">To TOP!</a></div>';
$html .= '<a name="top"></a><br /><br />';
$html .= '<table border="1" cellpadding="5" width="100%"><thead><tr><th>DT</th><th>Encoding</th><th>Parser</th><th>Result</th></tr></thead><tbody>' . PHP_EOL;

foreach ($dts as $keyDt => $_dt) {
	$dtd_count = 0;
	foreach ($encodings as $keyEnc => $_encoding) {
		$enc_count = 0;
		foreach ($filelistNew as $keyFile => $file) {
			// for debug
			// $html .= '<!-- '.$file['dt'].' - '.$file['encoding'].' -->';
			if ($file['dt'] === $_dt && $file['encoding'] === $_encoding) {

				$methods = AnalyzerOptions::get();

				// classes for debug
				$html .= "<tr class='$_dt $_encoding num_$keyDt$keyEnc file_$keyFile'>" . PHP_EOL;
				if ($dtd_count === 0) {
					$html .= '<th rowspan="'.(count($methods)*count($encodings)).'">'.strtoupper($file['dt']).'</th>' . PHP_EOL;
				}
				if ($enc_count === 0) {
					$html .= '<td rowspan="'.count($methods).'">'.strtoupper($file['encoding']).'<br /><a href="'.$file['url'].'">&raquo;&#187;file</a></td>' . PHP_EOL;
				}
				$dtd_count++;
				$enc_count++;

				foreach ($methods as $key => $parser) {
					$count = 0;

					$go->setBaseUri($file['url']);
					$content = "";
					if ($key !== AnalyzerOptions::PARSE_WITH_SAX_STREAM &&
						$key !== AnalyzerOptions::PARSE_WITH_XMLREADER_STREAM &&
						$key !== AnalyzerOptions::PARSE_WITH_SIMPLE_XMLREADER &&
						$key !== AnalyzerOptions::PARSE_WITH_PHANTOMJS) {
						$content = $go->getContent(Analyzer::CLIENT_NO_CLIENT, $file['url']);
					}

					$result = $go->testParser($content, $key, getDocType($_dt), $_encoding);
					$links = '';
					foreach ((array) $result['links'] as $link) {
						$links .= '<a href="'.$link['href'].'">'. $link['text']. '</a><br />';
					}
					if ($count > 0) {
						$html .= '<tr>';
					}
					if (!$result) {
						$html .= '<td>'.$parser['name'].'</td><td>not supported doctype</td></tr>' . PHP_EOL;
					} else {
						$html .= '<td>'.$result['name'].'</td><td>'.$links.'</td></tr>' . PHP_EOL;
					}
					$count++;
				}
			}
		}
	}
}
$html .= '</tbody></table>';

// Auto-convert encoding ways
// mb_detect_encoding($url, mb_detect_order(), true), "UTF-8", $url)
// ERRORS MAY BE OCCUR
// mb_convert_encoding($url, 'utf-8', mb_detect_encoding($url))


$handle = fopen($cachePage, 'w+') or die("Can't create file!");
fwrite($handle, $html);
fclose($handle);

ob_end_flush();
if (headers_sent()) {
	var_dump(headers_list());
} else {
	// For correct redirect:
	// Don't call echo, var_dump, flush and other in code above and includes
	// Encoding this file to UTF-8 without BOM
	header('Location: '.$appRootUrl.'/resources/pages/'.$cachePage.'?nocache='.time(), true, 307);
	die();
}

