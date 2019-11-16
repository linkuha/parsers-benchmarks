<?php

namespace parsing\wrappers;

use helpers\JsonEncodingWrapper;
use helpers\SystemHelper;

class PhantomjsParser
{
	public static function parseLinksJS($url, $contentType = 'text/html') {

		if ($contentType === 'text/xml') {
			$test = 'XML';
		} else {
			$test = 'HTML';
		}

		$os = SystemHelper::getOS();
		$progName		=	"phantomjs";
		$progName		=	$os === "windows" ? ($progName . ".exe") : $progName;

		$DS 			=	DIRECTORY_SEPARATOR;
		$progPath		=	dirname(dirname(dirname(__DIR__))) . "{$DS}bin{$DS}{$progName}";
		$scriptPath		=	dirname(dirname(dirname(__DIR__))) . "{$DS}resources{$DS}phantomjs";
		$fullCommand	=	$progPath . " {$scriptPath}{$DS}test{$test}.js {$url}";

		$res = shell_exec(escapeshellcmd($fullCommand));
		//@flush();	//
		if ($res) {
			$json = JsonEncodingWrapper::json_decode($res, true);
			return $json;
		} else {
			return null;
		}
	}

}