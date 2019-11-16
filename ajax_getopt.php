<?php
require_once 'vendor/autoload.php';
require_once "autoload.php";

use helpers\JsonEncodingWrapper;

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' === $_SERVER['HTTP_X_REQUESTED_WITH']) {
	$iterations = isset($_POST['iterations']) ? (int) $_POST['iterations'] : '';

	$DS = DIRECTORY_SEPARATOR;
	$outputDir 		= 	__DIR__.	"{$DS}resources{$DS}export{$DS}csv";
	$outputFile 	=	$outputDir. "{$DS}result__{$iterations}_iter.csv";
	$handle = fopen($outputFile, 'w+');
	if (!$handle) {
		throw new \Exception("Can't create or open a file, or file open in other app!");
	}
	fwrite($handle, "\xEF\xBB\xBF");
	fclose($handle);

	echo JsonEncodingWrapper::json_encode([
		'options' => AnalyzerOptions::get()
	]);
} else {
	echo 'hui';
}
