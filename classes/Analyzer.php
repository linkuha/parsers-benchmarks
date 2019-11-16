<?php

use Psr\Http\Message\UriInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\TransferStats;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Handler;
use helpers\UriHelper;
use helpers\DOMHelper;
use parsing\wrappers;
use profiling\Tester;
use profiling\Timer;
use JonnyW\PhantomJs\Client as PhantomClient;
use JonnyW\PhantomJs\ClientInterface as PhantomClientInterface;
use JonnyW\PhantomJs\DependencyInjection\ServiceContainer;

use AnalyzerOptions as AO;

class Analyzer
{
	const MAX_INPUT_SIZE	= 209715200; //bytes = 200 MB
	/**
	 * Real Media Types
	 */
	const MEDIATYPE_HTML	= 'text/html';
	const MEDIATYPE_XHTML	= 'application/xhtml+xml';
	const MEDIATYPE_XML		= 'text/xml';

	const CLIENT_NO_CLIENT	= 0;
	const CLIENT_GUZZLE		= 1;
	const CLIENT_PHANTOMJS	= 2;

	private $methods;

	/**
	 * @var GuzzleClient
	 */
	private $clientGuzzle;
	private $lastGuzzleTime;
	private $lastGuzzleSize;

	/**
	 * @var PhantomClient
	 */
	private $clientPhantom;
	private $lastPhantomTime;
	private $lastPhantomSize;

	/**
	 * @var UriInterface
	 */
	public $baseUri;
	//public $content;

	const MAX_ITERATIONS = 1000;
	private $testIterations;

	public $results = array();

	public function __construct()
	{
		$this->methods = AO::get();
		$this->testIterations = 1;

		if (AO::isEnabled(AO::PARSE_WITH_PHANTOMJS_PHP)) {
			if (isset($this->clientPhantom)) {
				unset($this->clientPhantom);
			}
			$this->clientPhantom = &$this->createPhantomClient();
		}
	}

	public function setBaseUri($url)
	{
		try {
            $this->baseUri = new Uri($url);
        } catch (Exception $e) {
			$this->baseUri = null;
		}
		return $this;
	}

	public function setIterations($count)
	{
		if (!is_int($count)) {
			throw new \InvalidArgumentException('The count must be a integer value.');
		} else {
			if ($count > self::MAX_ITERATIONS) {
				throw new \InvalidArgumentException(sprintf('The count must lower than %d.', self::MAX_ITERATIONS));
			}
		}
		$this->testIterations = $count;
		return $this;
	}

	public function initialize()
	{
		$optionsGuzzle = [
			RequestOptions::TIMEOUT  => 5.0,
			RequestOptions::CONNECT_TIMEOUT => 15,
			// Shared cookie jar for all requests.
			//RequestOptions::COOKIES => new CookieJar(),
			RequestOptions::HTTP_ERRORS => false,
			RequestOptions::ALLOW_REDIRECTS => [
				'max' => 1,
				'referer' => true,
				'track_redirects' => true,
			],
			RequestOptions::SYNCHRONOUS => true,
		];
		$options = isset($this->baseUri) ? array_merge($optionsGuzzle, [
			// Base URI is used with relative requests
			'base_uri' => new Uri(Uri::composeComponents($this->baseUri->getScheme(), $this->baseUri->getAuthority(), '', '', ''))
			]) : $optionsGuzzle;

		if (isset($this->clientGuzzle)) {
			unset($this->clientGuzzle);
		}
		$this->clientGuzzle = new GuzzleClient($options);
		return $this;
	}

	public function createPhantomClient($debug = false, $fullPathToBin = '')
	{
		$client = PhantomClient::getInstance();

		if ("" !== $fullPathToBin) {
			if (is_string($fullPathToBin)) {
				$client->getEngine()->setPath($fullPathToBin);
			} else {
				throw new \InvalidArgumentException('Full path to bin must be a string.');
			}
		}
		$client->getEngine()->debug($debug);
		return $client;
	}

	public function getResponsePhantom(PhantomClientInterface $client, $url, $delay = 0, $images = true, $procedures = []) {
		$client->getEngine()->addOption('--load-images=' . ($images ? 'true' : 'false') );
		//$client->getEngine()->addOption('--output-encoding=utf8');

		if (!empty($procedures)) {
			$location = __DIR__ . '\..\resourses\phantomjs';
			$serviceContainer = ServiceContainer::getInstance();

			$procedureLoader = $serviceContainer->get('procedure_loader_factory')
				->createProcedureLoader($location);
			foreach ($procedures as $proc) {
				if (is_string($proc))
					$client->setProcedure($proc);
			}

			$client->getProcedureLoader()->addLoader($procedureLoader);
			//$client->getProcedureCompiler()->disableCache();
		}

		$request = $client->getMessageFactory()->createRequest($url, 'GET');
		$response = $client->getMessageFactory()->createResponse();
		$request->setDelay($delay); //seconds

		return $client->send($request, $response);
	}

	public function getResponseGuzzle(UriInterface $uri) {

		return $this->clientGuzzle->send(
			new Request('GET', $uri), [
			'on_stats' => function (TransferStats $stats) {
				$this->lastGuzzleTime = $stats->getTransferTime();
				$this->lastGuzzleSize = Tester::sizeToString($stats->getHandlerStats()['size_download']);
			}]
		);
	}

	private function getMediaType($contentType)
	{
		switch ($contentType)
		{
			case AO::SUPPORTS_AUTO:		return null;
			case AO::SUPPORTS_HTML:		return self::MEDIATYPE_HTML;
			case AO::SUPPORTS_XHTML:	return self::MEDIATYPE_XHTML;
			case AO::SUPPORTS_XML:		return self::MEDIATYPE_XML;
			default: return null;
		}
	}

	public function getContent($client = self::CLIENT_GUZZLE, $uri = null, $maxlen = self::MAX_INPUT_SIZE)
	{
		$content = null;
		clearstatcache();	// real path params: true, $uri
		if (is_file($uri)) {
			if (self::CLIENT_NO_CLIENT === $client) {
				$content = file_get_contents($uri, false, null, 0, $maxlen);
				if (!$content) {
					throw new \Exception('Unable to load (file_get_contents)');
				}
				return $content;
			}
		} else {
			if (null === $uri) {
				if (!$this->baseUri) {
					throw new \Exception('Uri and Base uri is not set.');
				} else {
					$uri = $this->baseUri;
				}
			} else {
				if (false !== $uri2 = UriHelper::isValidUrl($uri)) {
					$uri = new Uri($uri2);
				} else {
					throw new \Exception('File not exists or incorrect url :: ' . $uri);
				}
			}
			if (self::CLIENT_NO_CLIENT === $client) {
				if (ini_get('allow_url_fopen') == false) {
					$success = ini_set('allow_url_fopen', true);
					if (!$success) {
						throw new \Exception('Error of file_get_content :: '. $uri);
					}
				}
				//ini_set('user_agent', 'azaza');
				$content = file_get_contents($uri, false, null, 0, $maxlen);
				if (!$content) {
					throw new \Exception('Unable to load (file_get_contents)');
				}
			}
			if (self::CLIENT_GUZZLE === $client && isset($this->clientGuzzle)) {
				$content = (string) $this->getResponseGuzzle(new Uri($uri))->getBody();
			}
			if (self::CLIENT_PHANTOMJS === $client && isset($this->clientPhantom)) {
				$content = $this->getResponsePhantom($this->clientPhantom, $uri, 0, false)->getContent();
			}
			return $content;
		}

	}


	public function testParser($content, $method, $docType = AO::SUPPORTS_HTML, $inputEncoding = 'UTF-8')
	{
		$timer = new Timer();

		if (!$content && !isset($this->baseUri)) return null;

		$info = AO::getInfo($method);

		$this->results = array();
		$this->results['name']	= $info['name'];
		$this->results['api']	= $info['api'];

		$contentType = $this->getMediaType($docType);
		if ($contentType === null) {
			if ($info['autodetectType'] === false) {
				$contentType = self::detectMediaType((string) $this->baseUri, true);
			}
		}

		if ($contentType === self::MEDIATYPE_HTML && $info['canHtml'] === false) return null;
		if ($contentType === self::MEDIATYPE_XHTML && $info['canXhtml'] === false) return null;
		if ($contentType === self::MEDIATYPE_XML && $info['canXml'] === false) return null;

		$className = $info['class'];

		$function = array();
		$arguments = array();
		switch ($method) {

			case AO::PARSE_WITH_XMLREADER:
			case AO::PARSE_WITH_SIMPLE_XML:
			case AO::PARSE_WITH_SAX_STRUCT:
			case AO::PARSE_WITH_REGEXP:
			case AO::PARSE_WITH_STRING_FUNCTIONS:
			case AO::PARSE_WITH_DIDOM_XPATH:
				$function = [$className, 'parseLinks'];
				$arguments = [$content, $contentType, $inputEncoding];
				break;

			case AO::PARSE_WITH_DIDOM_CSS:
				$function = [$className, 'parseLinks'];
				$arguments = [$content, $contentType, $inputEncoding, true];
				break;

			case AO::PARSE_WITH_HTML5LIB:
			case AO::PARSE_WITH_GANON:
				$function = [$className, 'parseLinks'];
				$arguments = [$content, $inputEncoding];
				break;

			case AO::PARSE_WITH_DOM_XPATH:
			case AO::PARSE_WITH_ZEND_DOM_XPATH:
			case AO::PARSE_WITH_SYMFONY_XPATH:
			case AO::PARSE_WITH_ADV_HTML_DOM_XPATH:
			case AO::PARSE_WITH_PHPQUERY:
			case AO::PARSE_WITH_QUERY_PATH:
			case AO::PARSE_WITH_FLUENTDOM_XPATH:
			case AO::PARSE_WITH_FDOMDOCUMENT_XPATH:
				$function = [$className, 'parseLinks'];
				$arguments = [$content, $contentType];
				break;

			case AO::PARSE_WITH_DOM:
			case AO::PARSE_WITH_ZEND_DOM_CSS:
			case AO::PARSE_WITH_SYMFONY_CSS:
			case AO::PARSE_WITH_ADV_HTML_DOM_CSS:
			case AO::PARSE_WITH_FLUENTDOM_CSS:
			case AO::PARSE_WITH_FDOMDOCUMENT_CSS:
				$function = [$className, 'parseLinks'];
				$arguments = [$content, $contentType, true];
				break;

			case AO::PARSE_WITH_NOKOGIRI:
			case AO::PARSE_WITH_SIMPLE_HTML_DOM:
				$function = [$className, 'parseLinks'];
				$arguments = [$content];
				break;

			case AO::PARSE_WITH_SABREXML:
			case AO::PARSE_WITH_SERVO_FLUIDXML:
				$function = [$className, 'parseLinks'];
				$arguments = [$content];
				break;

			case AO::PARSE_WITH_SAX_STREAM:
				$function = [new $className, 'parseLinksStream'];
				$arguments = [(string) $this->baseUri, 1];
				break;

			case AO::PARSE_WITH_XMLREADER_STREAM:
				$function = [$className, 'parseLinksStream'];
				$arguments = [(string) $this->baseUri];
				break;

			case AO::PARSE_WITH_PHANTOMJS_PHP:
				break;
		}

		$links = array();

		$timer->start($method);

		// Память только под malloc, не учитывает статичные переменные, что нам и нужно
		// поскольку все решения получается подключены к проекту
		//$memStart = memory_get_usage();

		// http://stackoverflow.com/a/16245036
		$memStart = memory_get_peak_usage(false);
		$memHighest = $memStart;
		$memDifHighest = 0;
		$memDif = 0;

		for ($i=0; $i < $this->testIterations; $i++) {
			unset($links['data']);

			if (AO::PARSE_WITH_PHANTOMJS === $method && $this->testIterations < 10) {
				$function = array($className, 'parseLinksJS');
				if (is_callable($function)) {
					$res = $function((string) $this->baseUri, $contentType);
					$links['data'] = $res['data'];
					$this->lastPhantomTime = $res['loading'] / 1000;
					$this->lastPhantomSize = Tester::sizeToString($res['size']);
				}
				if ($this->testIterations > 1) {
					usleep(10000);
				}

			} elseif (AO::PARSE_WITH_SIMPLE_XMLREADER === $method) {
				$reader = new $className();
				//не работает, если вернулся 404 код с телом страницы
				$reader->open((string) $this->baseUri);
				$reader->parse();
				$reader->close();
				$links['data'] = $reader->getData();
			}
			else {
				if (is_callable($function)) {
					$links['data'] = call_user_func_array($function, $arguments);
				}
			}

			$memUsed = memory_get_peak_usage(false);
			$memDif = $memUsed - $memStart;
			$memDifHighest = $memDif > $memDifHighest ? $memDif : $memDifHighest;
			$memHighest = $memUsed > $memHighest ? $memUsed : $memHighest;
		}

		$this->results['memory'] = $memDifHighest;
		$timer->stop($method);
		$this->results['time'] = $timer->getElapsedTime($method);
		$this->results['links'] = $links['data'] ? $links['data'] : null; // PHP 7: $links['data'] ?? null;

		//echo "</br> Guzzle load: $this->lastGuzzleTime s, download ($this->lastGuzzleSize)";
		//echo "</br> Phantom load: $this->lastPhantomTime s";

		return $this->results;
	}

	public function analyze()
	{
		echo "Кодировка PHP.ini по умолчанию: " . ini_get('default_charset');

		$parser = new wrappers\ZendParser($this->clientGuzzle);

		$pages = $parser->parseSiteLinksIterating(BASE_URL);

		//var_dump($pages);

		echo "Страница загружена за: " . Tester::getLoadTime();

		return $pages;
	}


	public static function detectMediaType($document, $isUrl = false)
	{
		// All media types
		// http://www.iana.org/assignments/media-types/media-types.xhtml

		if ($isUrl) {
			$document = self::getContent(self::CLIENT_NO_CLIENT, $document, 4096);
		}

		$part = substr(trim($document), 0, 300);
		if ('<' . '?xml' == substr($part, 0, 5)) {
			// XHTML
			// https://www.w3.org/TR/xhtml-media-types/
			if (preg_match('/<html[^>]*xmlns="([^"]+)"[^>]*>/i', $document, $matches)) {
				$xpathNamespace = $matches[1];
				return 'application/xhtml+xml';
			}
			// XML
			// RFC 3023 https://tools.ietf.org/html/rfc3023 (newer than RFC 2376)
			return 'text/xml';
		}
		if (strstr($part, 'DTD XHTML')) {
			return 'application/xhtml+xml';
		}
		return 'text/html';
	}
}
