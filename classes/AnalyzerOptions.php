<?php

class AnalyzerOptions
{
	// Flags
	const SUPPORTS_HTML 	=	1;
	const SUPPORTS_XHTML	=	2;
	const SUPPORTS_XML		=	4;
	const SUPPORTS_AUTO		=	8;

	// Selectors
	const SELECTOR_OWN		=	1;
	const SELECTOR_XPATH	=	2;
	const SELECTOR_CSS		=	4;

	// Types
	const TYPE_DOM_TREE		=	'Структура в памяти';
	const TYPE_STREAM_PULL	=	'Потоковый';
	const TYPE_STREAM_PUSH	=	'Потоковый';
	const TYPE_SEARCH		=	'Зависит от реализации';
	const TYPE_MIXED		=	'Гибридный';

	// Native APIs and functions
	const PARSE_WITH_DOM 				= 10;
	const PARSE_WITH_DOM_XPATH			= 15;
	const PARSE_WITH_SAX_STRUCT			= 20;
	const PARSE_WITH_SAX_STREAM			= 25;
	const PARSE_WITH_XMLREADER			= -30; // HTML с tidy, плохо обрабатывает
	const PARSE_WITH_XMLREADER_STREAM	= 35;
	const PARSE_WITH_SIMPLE_XML			= 40;
	const PARSE_WITH_REGEXP				= 50;
	const PARSE_WITH_STRING_FUNCTIONS	= 60;

	// HTML basically
	const PARSE_WITH_ZEND_DOM_XPATH		= 100;
	const PARSE_WITH_ZEND_DOM_CSS		= 110;
	const PARSE_WITH_SYMFONY_XPATH		= 120;
	const PARSE_WITH_SYMFONY_CSS		= 130;
	const PARSE_WITH_DIDOM_XPATH		= 140;
	const PARSE_WITH_DIDOM_CSS			= 145;
	const PARSE_WITH_SIMPLE_HTML_DOM	= 150;
	const PARSE_WITH_ADV_HTML_DOM_XPATH	= 160;
	const PARSE_WITH_ADV_HTML_DOM_CSS	= 165;
	const PARSE_WITH_NOKOGIRI			= 170;
	const PARSE_WITH_HTML5LIB			= 180;
	const PARSE_WITH_GANON				= 190;
	const PARSE_WITH_PHPQUERY			= 200;
	const PARSE_WITH_QUERY_PATH			= 210;
	const PARSE_WITH_FLUENTDOM_XPATH	= 220;
	const PARSE_WITH_FLUENTDOM_CSS		= 225;
	const PARSE_WITH_FDOMDOCUMENT_XPATH	= 230;
	const PARSE_WITH_FDOMDOCUMENT_CSS	= 235;

	// XML
	const PARSE_WITH_SIMPLE_XMLREADER	= 300; // --
	const PARSE_WITH_SABREXML			= -310; // todo
	const PARSE_WITH_SERVO_FLUIDXML		= -320; // todo

	// WebKit browser client (JavaScript support)
	const PARSE_WITH_PHANTOMJS			= 500;
	const PARSE_WITH_PHANTOMJS_PHP		= -510; // it's php library for using PhantomJS

	public function __invoke()
	{
		return self::get();
	}

	public static function getConstName($const)
	{
		$class = new ReflectionClass(__CLASS__);
		$constants = array_flip($class->getConstants());

		return $constants[$const];
	}

	public static function isEnabled($const)
	{
		return $const > 0 ? true : false;
	}

	public static function get()
	{
		return array_filter(self::getAll(), function($value, $key) {
			return $value['enabled'];
		}, ARRAY_FILTER_USE_BOTH);
	}

	public static function getInfo($key)
	{
		$methods = self::getAll();
		$info = array();

		if (array_key_exists($key, $methods)) {
			if (isset($methods[$key]['parent'])) {
				$parentId = $methods[$key]['parent'];
				$info = [
					'name' => $methods[$key]['name'],
					'class' => $methods[$parentId]['class'],
					'type' => $methods[$parentId]['type'],
					'api' => $methods[$parentId]['api'],
					'desc' => $methods[$parentId]['description'],
					'autodetectType' => (boolean) ($methods[$parentId]['supports'] & self::SUPPORTS_AUTO),
					'autodetectEnc' => $methods[$parentId]['autodetectEnc'],
					'canHtml' => (boolean) ($methods[$parentId]['supports'] & self::SUPPORTS_HTML),
					'canXhtml' => (boolean) ($methods[$parentId]['supports'] & self::SUPPORTS_XHTML),
					'canXml' => (boolean) ($methods[$parentId]['supports'] & self::SUPPORTS_XML),
					'selectorCss' => (boolean) ($methods[$key]['selector'] & self::SELECTOR_CSS),
					'selectorXpath' => (boolean) ($methods[$key]['selector'] & self::SELECTOR_XPATH),
					'link' => $methods[$parentId]['link'],
				];
			} else {
				$info = [
					'name' => $methods[$key]['name'],
					'class' => $methods[$key]['class'],
					'type' => $methods[$key]['type'],
					'api' => $methods[$key]['api'],
					'desc' => $methods[$key]['description'],
					'autodetectType' => (boolean) ($methods[$key]['supports'] & self::SUPPORTS_AUTO),
					'autodetectEnc' => $methods[$key]['autodetectEnc'],
					'canHtml' => (boolean) ($methods[$key]['supports'] & self::SUPPORTS_HTML),
					'canXhtml' => (boolean) ($methods[$key]['supports'] & self::SUPPORTS_XHTML),
					'canXml' => (boolean) ($methods[$key]['supports'] & self::SUPPORTS_XML),
					'selectorCss' => (boolean) ($methods[$key]['selector'] & self::SELECTOR_CSS),
					'selectorXpath' => (boolean) ($methods[$key]['selector'] & self::SELECTOR_XPATH),
					'link' => $methods[$key]['link'],
				];
			}
			foreach ($methods as $k => $v) {
				if (isset($methods[$k]['parent']) && $key === $methods[$k]['parent']) {
					$css = (boolean) ($v['selector'] & self::SELECTOR_CSS);
					$xpath = (boolean) ($v['selector'] & self::SELECTOR_XPATH);
					$info['selectorCss'] = $css ? true : $info['selectorCss'];
					$info['selectorXpath'] = $xpath ? true : $info['selectorXpath'];
				}
			}
			return $info;
		}
		return null;
	}

	private static function getAll()
	{
		return [
			self::PARSE_WITH_DOM => [
				'enabled' => self::isEnabled(self::PARSE_WITH_DOM),
				'name' => 'DOMDocument',
				'class' => parsing\wrappers\DomParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_OWN,
				'description' => 'DOMDocument',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.dom.php',
			],
			self::PARSE_WITH_DOM_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_DOM_XPATH),
				'parent' => self::PARSE_WITH_DOM,
				'name' => 'DOMDocument + DOMXPath',
				'selector' => self::SELECTOR_XPATH,
			],
			self::PARSE_WITH_XMLREADER => [
				'enabled' => self::isEnabled(self::PARSE_WITH_XMLREADER),
				'name' => 'XMLReader + Tidy',
				'class' => parsing\wrappers\XMLReaderParser::class,
				'type' => self::TYPE_MIXED,
				'api' => 'XMLReader*',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.xmlreader.php',
			],
			self::PARSE_WITH_XMLREADER_STREAM => [
				'enabled' => self::isEnabled(self::PARSE_WITH_XMLREADER_STREAM),
				'name' => 'XMLReader',
				'class' => parsing\wrappers\XMLReaderParser::class,
				'type' => self::TYPE_STREAM_PULL,
				'api' => 'XMLReader*',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.xmlreader.php',
			],
			self::PARSE_WITH_SIMPLE_XML => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SIMPLE_XML),
				'name' => 'SimpleXML (import DOMDocument)',
				'class' => parsing\wrappers\SimpleXmlParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'SimpleXML*',
				'selector' => self::SELECTOR_XPATH,
				'description' => 'Based on callbacks. Use only for xml files. Supports XPath',
				'supports' => self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.simplexml.php',
			],
			self::PARSE_WITH_REGEXP => [
				'enabled' => self::isEnabled(self::PARSE_WITH_REGEXP),
				'name' => 'Регулярные выражения',
				'class' => parsing\wrappers\RegularParser::class,
				'type' => self::TYPE_SEARCH,
				'api' => 'PCRE',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/PCRE',
			],
			self::PARSE_WITH_STRING_FUNCTIONS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_STRING_FUNCTIONS),
				'name' => 'Функции обработки строк',
				'class' => parsing\wrappers\StringParser::class,
				'type' => self::TYPE_SEARCH,
				'api' => 'PHP',
				'selector' => self::SELECTOR_OWN,
				'description' => 'Used only one regex',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/ref.strings.php',
			],
			self::PARSE_WITH_SAX_STRUCT => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SAX_STRUCT),
				'name' => 'Expat SAX (Tidy, создание структуры)',
				'class' => parsing\wrappers\ExpatParser::class,
				'type' => self::TYPE_MIXED,
				'api' => 'SAX*',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.xml.php',
			],
			self::PARSE_WITH_SAX_STREAM => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SAX_STREAM),
				'name' => 'Expat SAX (потоковый)',
				'class' => parsing\wrappers\ExpatParser::class,
				'type' => self::TYPE_STREAM_PUSH,
				'api' => 'SAX*',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.xml.php',
			],

			self::PARSE_WITH_ZEND_DOM_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_ZEND_DOM_XPATH),
				'name' => 'Zend Dom Query (XPath)',
				'class' => parsing\wrappers\ZendParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => '',
				'supports' => self::SUPPORTS_AUTO | self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'https://github.com/zendframework/zend-dom',
			],
			self::PARSE_WITH_ZEND_DOM_CSS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_ZEND_DOM_CSS),
				'parent' => self::PARSE_WITH_ZEND_DOM_XPATH,
				'name' => 'Zend Dom Query (CSS)',
				'selector' => self::SELECTOR_CSS,
			],
			self::PARSE_WITH_SYMFONY_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SYMFONY_XPATH),
				'name' => 'Symfony 2.8 DomCrawler (XPath)',
				'class' => parsing\wrappers\SymfonyParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => '',
				'supports' => self::SUPPORTS_AUTO | self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => 'Разметка',
				'link' => 'http://symfony.com/doc/current/components/dom_crawler.html',
			],
			self::PARSE_WITH_SYMFONY_CSS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SYMFONY_CSS),
				'parent' => self::PARSE_WITH_SYMFONY_XPATH,
				'name' => 'Symfony 2.8 DomCrawler (CSS)',
				'selector' => self::SELECTOR_CSS,
			],
			self::PARSE_WITH_DIDOM_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_DIDOM_XPATH),
				'name' => 'DiDom (XPath)',
				'class' => parsing\wrappers\DidomParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => 'css / xpath support',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'https://github.com/imangazaliev/didom',
			],
			self::PARSE_WITH_DIDOM_CSS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_DIDOM_CSS),
				'parent' => self::PARSE_WITH_DIDOM_XPATH,
				'name' => 'DiDom (CSS)',
				'selector' => self::SELECTOR_CSS,
			],
			self::PARSE_WITH_SIMPLE_HTML_DOM => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SIMPLE_HTML_DOM),
				'name' => 'Simple HTML DOM',
				'class' => parsing\wrappers\SimpleHtmlParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM',
				'selector' => self::SELECTOR_CSS,
				'description' => 'Only public methods, defines. Realised only with string comparisons, arrays operations, some regular expressions.',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML,
				'autodetectEnc' => 'Разметка, библиотека MB',
				'link' => 'http://simplehtmldom.sourceforge.net/',
			],
			self::PARSE_WITH_ADV_HTML_DOM_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_ADV_HTML_DOM_XPATH),
				'name' => 'Advanced HTML DOM (XPath)',
				'class' => parsing\wrappers\AdvancedHtmlParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => 'Realised with many regexes to use CSS selectors functionality',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'https://github.com/monkeysuffrage/advanced_html_dom',
			],
			self::PARSE_WITH_ADV_HTML_DOM_CSS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_ADV_HTML_DOM_CSS),
				'parent' => self::PARSE_WITH_ADV_HTML_DOM_XPATH,
				'name' => 'Advanced HTML DOM (CSS)',
				'selector' => self::SELECTOR_CSS,
			],
			self::PARSE_WITH_NOKOGIRI => [
				'enabled' => self::isEnabled(self::PARSE_WITH_NOKOGIRI),
				'name' => 'Nokogiri PHP',
				'class' => parsing\wrappers\NokogiriParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_CSS,
				'description' => 'Simple class with using regexes to convert CSS selectors to XPath',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML,
				'autodetectEnc' => false,
				'link' => 'https://github.com/olamedia/nokogiri',
			],
			self::PARSE_WITH_HTML5LIB => [
				'enabled' => self::isEnabled(self::PARSE_WITH_HTML5LIB),
				'name' => 'HTML5lib PHP (SAX-like)',
				'class' => parsing\wrappers\HTML5libParser::class,
				'type' => self::TYPE_MIXED,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML,
				'autodetectEnc' => false,
				'link' => 'https://github.com/Masterminds/html5-php',
			],
			self::PARSE_WITH_PHPQUERY => [
				'enabled' => self::isEnabled(self::PARSE_WITH_PHPQUERY),
				'name' => 'phpQuery',
				'class' => parsing\wrappers\PhpQueryParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_CSS,
				'description' => '',
				'supports' => self::SUPPORTS_AUTO | self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => 'Разметка, библиотека MB',
				'link' => 'https://github.com/electrolinux/phpquery',
			],
			self::PARSE_WITH_QUERY_PATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_QUERY_PATH),
				'name' => 'QueryPath',
				'class' => parsing\wrappers\QueryPathParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*, SPL',
				'selector' => self::SELECTOR_CSS,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => 'Библиотека MB',
				'link' => 'https://github.com/technosophos/querypath',
			],
			self::PARSE_WITH_FLUENTDOM_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_FLUENTDOM_XPATH),
				'name' => 'Fluent DOM (XPath)',
				'class' => parsing\wrappers\FluentDomParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://fluentdom.github.io/',
			],
			self::PARSE_WITH_FLUENTDOM_CSS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_FLUENTDOM_CSS),
				'parent' => self::PARSE_WITH_FLUENTDOM_XPATH,
				'name' => 'Fluent DOM (CSS)',
				'selector' => self::SELECTOR_CSS,
			],
			self::PARSE_WITH_GANON => [
				'enabled' => self::isEnabled(self::PARSE_WITH_GANON),
				'name' => 'Ganon',
				'class' => parsing\wrappers\GanonParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'PHP',
				'selector' => self::SELECTOR_CSS,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML,
				'autodetectEnc' => 'Разметка*',
				'link' => 'https://code.google.com/archive/p/ganon/',
			],
			self::PARSE_WITH_FDOMDOCUMENT_XPATH => [
				'enabled' => self::isEnabled(self::PARSE_WITH_FDOMDOCUMENT_XPATH),
				'name' => 'fDOMDocument (XPath)',
				'class' => parsing\wrappers\FDomDocumentParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM*',
				'selector' => self::SELECTOR_XPATH,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'https://github.com/theseer/fDOMDocument',
			],
			self::PARSE_WITH_FDOMDOCUMENT_CSS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_FDOMDOCUMENT_CSS),
				'parent' => self::PARSE_WITH_FDOMDOCUMENT_XPATH,
				'name' => 'fDOMDocument (CSS)',
				'selector' => self::SELECTOR_CSS,
			],
			self::PARSE_WITH_SIMPLE_XMLREADER => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SIMPLE_XMLREADER),
				'name' => 'SimpleXMLReader',
				'class' => parsing\wrappers\SimpleXMLReaderParser::class,
				'type' => self::TYPE_STREAM_PULL,
				'api' => 'SimpleXML, XMLReader',
				'selector' => self::SELECTOR_OWN,
				'description' => 'Based on callbacks with using XMLReader. Can expand to SimpleXMLElement or DOMDocument Object, or XML string',
				'supports' => self::SUPPORTS_XML,
				'autodetectEnc' => false,
				'link' => 'http://php.net/manual/en/book.xmlreader.php',
			],
			self::PARSE_WITH_SABREXML => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SABREXML),
				'name' => 'Sabre XML',
				'class' => parsing\wrappers\SabreXMLParser::class,
				'type' => '',
				'api' => '',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_XML,
				'link' => 'http://sabre.io/xml/',
			],
			self::PARSE_WITH_SERVO_FLUIDXML => [
				'enabled' => self::isEnabled(self::PARSE_WITH_SERVO_FLUIDXML),
				'name' => 'Servo FluidXML',
				'class' => parsing\wrappers\FluidXMLParser::class,
				'type' => '',
				'api' => '',
				'selector' => self::SELECTOR_OWN,
				'description' => '',
				'supports' => self::SUPPORTS_XML,
				'link' => 'https://github.com/servo-php/fluidxml',
			],

			self::PARSE_WITH_PHANTOMJS => [
				'enabled' => self::isEnabled(self::PARSE_WITH_PHANTOMJS),
				'name' => 'PhantomJS',
				'class' => parsing\wrappers\PhantomjsParser::class,
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM (WebKit)',
				'selector' => self::SELECTOR_CSS,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'autodetectEnc' => 'Браузер',
				'link' => 'http://phantomjs.org/',
			],
			self::PARSE_WITH_PHANTOMJS_PHP => [
				'enabled' => self::isEnabled(self::PARSE_WITH_PHANTOMJS_PHP),
				'name' => 'PHP PhantomJS',
				'type' => self::TYPE_DOM_TREE,
				'api' => 'DOM (WebKit)',
				'selector' => self::SELECTOR_CSS,
				'description' => '',
				'supports' => self::SUPPORTS_HTML | self::SUPPORTS_XHTML | self::SUPPORTS_XML,
				'link' => 'http://jonnnnyw.github.io/php-phantomjs/',
			],
		];
	}
}