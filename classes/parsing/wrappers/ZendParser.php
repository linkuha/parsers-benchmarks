<?php

namespace parsing\wrappers;

use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Dom;
use parsing\BasePage;

class ZendParser
{
	const SPIDER_LIMIT = 30;

	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $client = null;

	private $pages = array();

	private $parsedUris = array();
	private $parsedUrls = array();

	/**
	 * @var \SplQueue
	 */
	private $queue;

	private $visited = array();

	/**
	 * @var array Kvadratnaya matrica
	 */
	private $adjacencyMatrix;

	private $counter;

	public function __construct($client)
	{
		ini_set('max_execution_time', 360);
		set_time_limit(0);

		$this->client = $client;

		$this->counter = 0;

		//TODO отцепить хост от http и другого. вылидация
	}

	public static function parseLinks($content, $contentType = 'text/html', $css = false, $hidden = false)
	{
		$harvest_links = array();

		// Creates DOMDocument (in execute() method)
		// version 1.0 and default encoding, if not specified in constructor

		if ($contentType === 'text/xml') {
			$isXml = true;

			$content = preg_replace('#<!DOCTYPE.*?>#i', '', $content);
			if (!$content) {
				throw new Exception('DOCTYPE removing error.');
			}
		} else {
			$isXml = false;
		}
		$dom = new Dom\Query($content);

		if ($css) {
			if ($isXml) {
				foreach ($dom->execute('offer') as $a) {
					$id = $a->getAttribute('id');

					$harvest_links[] = [
						'text' => $id,
						'href' => $a->firstChild->textContent,
					];
				}
			} else {
				foreach ($dom->execute('a') as $a) {
					$url = $a->getAttribute('href');
					$rel = $a->getAttribute('rel');

					if(isset($rel) && !empty($rel)) {
						$harvest_links[] = [
							'text' => $a->textContent,
							'href' => $url,
							'xpath' => $a->getNodePath(),
							'rel' => $rel,
						];
					} else {
						$harvest_links[] = [
							'text' => $a->textContent,
							'href' => $url,
							'xpath' => $a->getNodePath(),
						];
					}
				}
			}
		} else {
			if ($isXml) {
				foreach ($dom->queryXpath('//offer') as $a) {
					$id = $a->getAttribute('id');

					$harvest_links[] = [
						'text' => $id,
						'href' => $a->firstChild->textContent,
					];
				}
			} else {
				foreach ($dom->queryXpath('//a') as $a) {
					$url = $a->getAttribute('href');
					$rel = $a->getAttribute('rel');

					if(isset($rel) && !empty($rel)) {
						$harvest_links[] = [
							'text' => $a->textContent,
							'href' => $url,
							'xpath' => $a->getNodePath(),
							'rel' => $rel,
						];
					} else {
						$harvest_links[] = [
							'text' => $a->textContent,
							'href' => $url,
							'xpath' => $a->getNodePath(),
						];
					}
				}
			}
		}
		if ($hidden) {
			foreach ($dom->queryXpath('//noindex/*/a') as $link) {
				foreach ($harvest_links as &$one) {
					if ($one['xpath'] == $link->getNodePath()) {
						$one['noindex'] = true;
					}
				}
			}

		}
		unset($dom);
		return $harvest_links;
	}


	public function parseSiteLinksRecursive($url)
	{
		$uri = new Uri(rtrim($url, '/'));

		//if (!$this->isValidUri($uri)) return false;

		$request = new Request('GET', $uri);
		$response = $this->client->send($request, ['timeout' => 4]);

		//var_dump($response);

		$code = $response->getStatusCode(); // 200
		//$reason = $response->getReasonPhrase(); // OK

		$page = new BasePage($this->counter, $uri);
		$page->setCode($code);

		$this->pages[$this->counter] = $page;

		echo $response->getHeaderLine('X-Guzzle-Redirect-History') . PHP_EOL;
		if (200 !== $code) {
			echo ($this->counter+1) . ' -- ' . $uri . ' -- '. $code . PHP_EOL;
			$this->counter++;
			return false;
		}

		$html = (string) $response->getBody();
		$dom = new Dom\Query($html);

		$parsedNodes = $dom->execute('a');
		$this->parsedUris[] = $uri;

		$parsed_new = array();

		foreach ($parsedNodes as $node) {
			$url1 = rtrim($node->getAttribute('href'), '/');
			if (!empty($url1)) $parsed_new[] = $url1;
		}

		$parsed_new = array_keys(array_flip($parsed_new));
		$parsed_new = array_diff($parsed_new, $this->parsedUris);
		$this->parsedUris = array_merge($this->parsedUris, $parsed_new);

		echo ($this->counter+1) . '\t -- ' . $uri . ' -- '. $code . ' / parsed: ' . count($parsed_new) . ' from ' . count($parsedNodes) . '<br>';
		//echo '<pre>';
		//print_r($parsed_new);
		//echo '</pre>';

		if(self::SPIDER_LIMIT < $this->counter) return false;
		$this->counter++;

		foreach($parsed_new as $url2) {
			$this->parseSiteLinksRecursive($url2);
		}
	}



	public function parseSiteLinksIterating($url)
	{
		$url = rtrim($url, '/');
		//if (!$this->isValidUri($uri)) return false;

		// пустая очередь
		$queue = new \SplQueue();
		$queue->enqueue($url);

		$this->counter = 0;

		while (!$queue->isEmpty()) {
			$url_curr = $queue->dequeue();

			$uri_curr  = new Uri($url_curr);
			if (!$this->isValidUri($uri_curr)) continue;

			if (isset($this->pages[$url_curr])) {
				continue;
			}
			$request = new Request('GET', $uri_curr);
			$response = $this->client->send($request, ['timeout' => 4]);

			$code = $response->getStatusCode(); // 200

			$page = new BasePage($this->counter, $uri_curr);
			$page->setCode($code);

			$this->pages[$url_curr] = $page;

			var_dump($response->getHeaderLine('X-Guzzle-Redirect-History'));
			if (200 !== $code) {
				echo ($this->counter+1) . ' -- ' . rawurldecode($uri_curr) . ' -- '. $code . PHP_EOL;
				$this->counter++;
				continue;
			}

			$html = (string) $response->getBody();
			$dom = new Dom\Query($html);

			$parsedNodes = $dom->execute('a');

			foreach ($parsedNodes as $node) {
				$url1 = trim($node->getAttribute('href'));
				if (!empty($url1)) $queue->enqueue($url1);
			}

			echo ($this->counter+1) . '\t -- ' . rawurldecode($uri_curr) . ' -- code '. $code . ' count ' .
				count($parsedNodes) . '<br>';

			if(self::SPIDER_LIMIT < $this->counter) break;
			$this->counter++;
		}

		return $this->pages;
	}



}