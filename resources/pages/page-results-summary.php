<?php

function getOS() {
	$uname = strtolower(php_uname());
	if (strpos($uname, "darwin") !== false) { return 'macosx'; }
	elseif (strpos($uname, "win") !== false) { return 'windows'; }
	elseif (strpos($uname, "linux") !== false) { return 'linux'; }
	else { return 'unknown'; }
}
if ('windows' !== getOS() && PHP_SAPI == "cli") {
	shell_exec('chcp 65001');
}


$DS = DIRECTORY_SEPARATOR;
// Values separator
$VS = ';';

$iteratorRes = new FilesystemIterator(dirname(__DIR__) . $DS . 'export' . $DS . 'csv');
$filterRes = new RegexIterator($iteratorRes, "/test_.*\\.csv$/");
$resultsList = array();
foreach($filterRes as $entry) {
	$resultsList[] = [
		'name' => $entry->getFilename(),
		'path' => $entry->getPathname(),
	];
}

$resultsSummary = array();

foreach ($resultsList as $item) {
	preg_match('#test_([^\s]+)__(\d+)\.csv$#', $item['name'], $infoParts);
	$parsingPage = $infoParts[1];
	$iters = $infoParts[2];

	$lines = file($item['path']);
	if (!$lines) {
		throw new \Exception("Can't open a file for reading!");
	}

	$parser = array();
	foreach ($lines as $line_num => $line) {
		if ($line_num === 0 && substr($line, 0, 3) === "\xEF\xBB\xBF") {
			$line = substr($line, 3);
		}

		$parts = explode($VS, $line);

		$parser[trim($parts[0], '"')] = [
			'memory' => $parts[1],
			'time' => $parts[2],
		];
	}

	$resultsSummary[$parsingPage] = array_merge(
		$resultsSummary[$parsingPage] ?? [],
		[$iters => $parser]
	);
}
print_r($resultsSummary);