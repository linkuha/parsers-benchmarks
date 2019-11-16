<?php
require_once 'vendor/autoload.php';
require_once "autoload.php";

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH']) {
	$url = isset($_POST['url']) ? $_POST['url'] : '';
	$contentType = isset($_POST['markup']) ? (int) $_POST['markup'] : '';
	$method = isset($_POST['method']) ? (int) $_POST['method'] : '';
	$iterations = isset($_POST['iterations']) ? (int) $_POST['iterations'] : '';

	set_time_limit(240); // 4 min

	$go = new Analyzer();
	$go->setBaseUri($url)->initialize();

	// CSV values separator
	$VS = ';';
	$DS = DIRECTORY_SEPARATOR;
	$outputDir 		= 	__DIR__.	"{$DS}resources{$DS}export{$DS}csv";
	$outputFile 	=	$outputDir. "{$DS}result__{$iterations}_iter.csv";

	$content = "";
	if ($method !== AnalyzerOptions::PARSE_WITH_SAX_STREAM &&
		$method !== AnalyzerOptions::PARSE_WITH_XMLREADER_STREAM &&
		$method !== AnalyzerOptions::PARSE_WITH_SIMPLE_XMLREADER) {
		$content = $go->getContent();
		if (false === $content) {
			echo json_encode([
				'status' => 400,
			]);
			exit(1);
		}
	}
	try {
		$go->setIterations((int) $iterations);
		$result = $go->testParser($content, $method, $contentType);
		if (null === $result) {
			echo json_encode([
				'status' => 204,
				'message' => 'Parser not works with that format',
			]);
		} else {
			$handleW = fopen($outputFile, 'a+');
			if (!$handleW) {
				throw new \Exception("Can't create or open a file, or file open in other app!");
			}
			$res = '"' . $url . '"'
					. $VS . '"' . $result['name'] . '"'
					. $VS . $result['memory']
					. $VS . $result['time']
					. $VS . count($result['links']) . PHP_EOL;

			fwrite($handleW, $res);
			fclose($handleW);

			echo json_encode([
				'status' => 200,
				'data' => $result
			]);
		}
	} catch (\Exception $exc) {
		// If cURL error: https://curl.haxx.se/libcurl/c/libcurl-errors.html
		echo json_encode([
			'status' => 500,
			'message' => $exc->getMessage()
		]);
	}
} else {
	echo 'hui';
}
