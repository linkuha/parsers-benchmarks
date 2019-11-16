<?php
if (PHP_SAPI != "cli") {
	exit(1);
}
set_time_limit(300); // 5 min

const MAX_INPUT_SIZE	= 209715200; //bytes = 200 MB
const MAX_MEMORY_LIMIT	= '-1'; //'512M';

define('APP_ROOT', dirname(dirname(__DIR__)));
define('APP_ROOT_URL', 'http://seo.local');

//error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
error_reporting(0);
function shutdown() {
	$error = error_get_last();
	if ($error['type'] === E_ERROR) {
		// fatal error has occured
		echo 0 . PHP_EOL;
		echo 0 . PHP_EOL;
		//var_dump($error);
		exit (1); //error code
	}
}
register_shutdown_function('shutdown');

if($argc < 4) {
	echo "Usage: ".$argv[0]." <file> <type: html|xhtml|xml> <iterations>\n";
	exit(1);
}

$tmpNumber = $argv[4];
if (isset($tmpNumber) && ctype_digit($tmpNumber)) {
	$file = "parser_$tmpNumber.pid";
	$pid = getmypid();

	$handle = @fopen($file, 'w+');
	fwrite($handle, $pid);
	fclose($handle);
}

function signal_handler($signal)
{
	switch ($signal) {
		case SIGTERM:
		case SIGHUP:
		case SIGUSR1:
			echo 0 . PHP_EOL;
			echo 0 . PHP_EOL;
			exit;
		default:
			// handle all other signals
	}
}
function getOS()
{
	$uname = strtolower(php_uname());

	if (strpos($uname, "darwin") !== false) {
		return 'macosx';
	} elseif (strpos($uname, "win") !== false) {
		return 'windows';
	} elseif (strpos($uname, "linux") !== false) {
		return 'linux';
	} else {
		return 'unknown';
	}
}
if ('windows' !== getOS()) {
	pcntl_signal(SIGTERM, 'signal_handler');
	pcntl_signal(SIGHUP, 'signal_handler');
	//pcntl_signal(SIGUSR1, 'signal_handler');
}