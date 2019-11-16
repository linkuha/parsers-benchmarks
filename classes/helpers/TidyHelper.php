<?php

namespace helpers;

final class TidyHelper {

	/**
	 * @var string Path of installed program Html5-Tidy
	 */
	private static $prog_path = 'tidy'; //'C:\WEB\Tidy\bin\tidy.exe'; //

	/**
	 * Get valid content
	 *
	 * @param string $content
	 * @param string $outputType
	 * @param string $inputEncoding
	 * @return string
	 * @throws \Exception
	 * @link http://tidy.sourceforge.net/docs/quickref.html
	 */
	public static function getClean($content, $inputEncoding = 'UTF-8', $outputType = 'application/xhtml+xml')
	{
		// Better way to my mind is encode document to UTF-8 before give to tidy
		if ($inputEncoding !== 'UTF-8') {
			$content = iconv($inputEncoding, 'UTF-8', $content);
		}

		if (!self::isConfiguredInPhp())
			throw new \Exception('Extension tidy is not installed to PHP');

		if ('text/html' === $outputType) {
			$convertOption = 'output-html';
		} elseif ('application/xhtml+xml' === $outputType) {
			$convertOption = 'output-xhtml';
		} elseif ('text/xml' === $outputType) {
			$convertOption = 'output-xml';
		} else {
			throw new \Exception("Output content type is not recognized!");
		}

		# Tidy for PHP version 4
		if (substr(phpversion(), 0, 1) == 4) {
			tidy_setopt('uppercase-attributes', false);
			tidy_setopt('wrap', 800);
			tidy_parse_string($content);
			$cleaned_html = tidy_get_output();
		} else {
			$tidy = new \tidy();
			$cleaned_html = $tidy->repairString($content, [
				$convertOption		=> true,
				'doctype'			=> 'strict',
				'anchor-as-name'	=> false,
			//	'clean'				=> true,
				'numeric-entities'	=> false,
				'preserve-entities'	=> true,
				'quote-nbsp' 		=> true,
				'uppercase-attributes' => false,
				'uppercase-tags'	=> false,
				'wrap'                 => 0,
				'force-output'		=> true,
				'tidy-mark'			=> false,
				'hide-comments'		=> true,
				'new-inline-tags'	=> 'noindex',		// For Yandex.ru
				'output-bom'		=> false,
			//	Not correctly works options	:'(
			//	'char-encoding'		=> 'ascii',			// Default: ascii (overrides both following)
			//	'input-encoding'	=> $inputEncoding,	// Default: latin1
			//	'output-encoding'	=> 'utf8',			// Default: ascii
			], 'utf8');
		}
		return $cleaned_html;
	}

	public static function getReleaseDate() {
		$tidy = new \tidy;
		$version = $tidy->getRelease();
		unset($tidy);
		return $version;
	}

	/**
	 * @param string $content
	 * @param string $outputType
	 * @param string $inputEncoding
	 * @return string
	 * @throws \Exception
	 * @link http://api.html-tidy.org/tidy/quickref_5.2.0.html
	 */
	public static function getCleanHtml5($content, $inputEncoding = 'UTF-8', $outputType = 'application/xhtml+xml') {

		//$inputEncoding = self::convertEncoding($inputEncoding);
		if ($inputEncoding !== 'UTF-8') {
			$content = iconv($inputEncoding, 'UTF-8', $content);
		}

		if ('text/html' === $outputType) {
			$convertOption = '-ashtml';
		} elseif ('application/xhtml+xml' === $outputType) {
			$convertOption = '-asxhtml';
		} elseif ('text/xml' === $outputType) {
			$convertOption = '-asxml';
		} else {
			throw new \Exception("Output content type is not recognized!");
		}

		$DS 			=	DIRECTORY_SEPARATOR;
		$tempDir 		= 	__DIR__.	"{$DS}..{$DS}..{$DS}runtime{$DS}tmp";
		$inputFile 		=	$tempDir. 	"{$DS}tidy-last-in.html";
		$outputFile 	=	$tempDir .	"{$DS}tidy-last-out.html";
		$outputLogFile 	=	$tempDir .	"{$DS}tidy-last-output.txt";
		$configFile 	=	__DIR__ . "{$DS}..{$DS}..{$DS}config{$DS}tidy.conf";

		$handle = fopen($inputFile, 'w+');
		if (!$handle) {
			throw new \Exception("Can't create or open a file!");
		}
		fwrite($handle, $content);
		fclose($handle);

		$command = self::$prog_path . ' -utf8 ' . $convertOption .
			' -config ' . $configFile .
			' -file ' . $outputLogFile . ' ' .
			$inputFile . ' > ' . $outputFile;

		$res = shell_exec($command);
		if (strpos($res, 'Error: ') === false) {
			return file_get_contents($outputFile);
		} else {
			throw new \Exception("Tidy error, see output file!");
		}
	}

	public static function isConfiguredInPhp() {
		// Detect if Tidy is in configured
		// function_exists('tidy_get_release')
		return extension_loaded('tidy');
	}

	/**
	 * Convert encoding name to tidy's format
	 *
	 * Allowed: US ASCII (default), ISO Latin-1, Latin-0, Win1252, UTF-8
	 * and the ISO 2022 family of 7 bit encodings.
	 * format: ascii, latin1, latin0, win1252, utf8
	 *
	 * @param string $inputEncoding
	 * @return string
	 */
	private static function convertEncoding($inputEncoding) {
		switch (strtoupper($inputEncoding)) {
			case 'ASCII':
			case 'US-ASCII': $inputEncoding = 'ascii';
				break;
			case 'UTF-8':
			case 'UTF8': $inputEncoding = 'utf8';
				break;
			case 'ISO-8859-1': $inputEncoding = 'latin1';
				break;
			case 'ISO-8859-15': $inputEncoding = 'latin0';
				break;
			case 'CP1252':
			case 'WINDOWS-1252': $inputEncoding = 'win1252';
				break;
			default: $inputEncoding = 'latin1';
		}
		return $inputEncoding;
	}
}