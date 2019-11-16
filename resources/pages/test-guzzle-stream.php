<?php

require_once "../../vendor/autoload.php";
require_once "../../autoload_test.php";

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler\StreamHandler;
use GuzzleHttp\HandlerStack;

$path = 'C:\WEB\OpenServer\domains\seo.local\resources\encoded-doc\cp1251\html4.html';
$url = 'http://'.$_SERVER['HTTP_HOST'].'/resources/encoded-doc/cp1251/html4.html';

$handler = new StreamHandler();
$stack = HandlerStack::create($handler); // Wrap w/ middleware

$client = new GuzzleClient([
	//RequestOptions::TIMEOUT  => 5.0,
	//RequestOptions::CONNECT_TIMEOUT => 15,
	// Shared cookie jar for all requests.
	RequestOptions::COOKIES => new CookieJar(),
	RequestOptions::HTTP_ERRORS => false,
	RequestOptions::ALLOW_REDIRECTS => [
		'max' => 1,
		'referer' => true,
		'track_redirects' => true,
	],
	//RequestOptions::SYNCHRONOUS => true,
	'handler' => $stack,

]);

$response = $client->send(new Request('GET', $url));

$html = (string) $response->getBody();

echo $html;