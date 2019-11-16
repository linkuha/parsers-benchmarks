<?php

namespace helpers;

final class JsonEncodingWrapper
{
	/**
	 * Wrapper for json_decode that throws when an error occurs.
	 *
	 * @param string $json    JSON data to parse
	 * @param bool $assoc     When true, returned objects will be converted
	 *                        into associative arrays.
	 * @param int    $depth   User specified recursion depth.
	 * @param int    $options Bitmask of JSON decode options.
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException if the JSON cannot be decoded.
	 * @link http://www.php.net/manual/en/function.json-decode.php
	 */
	public static function json_decode($json, $assoc = false, $depth = 512, $options = 0)
	{
		if (empty($json)) return false;
		$data = \json_decode($json, $assoc, $depth, $options);
		if (JSON_ERROR_NONE !== json_last_error()) {
			$DS 			=	DIRECTORY_SEPARATOR;
			$tempDir 		= 	__DIR__.	"{$DS}..{$DS}..{$DS}runtime{$DS}log";
			$outputLogFile 	=	$tempDir .	"{$DS}json-last-errors.log";

			$handle = fopen($outputLogFile, 'w+');
			if ($handle) {
				fwrite($handle, 'json_decode error: ' . json_last_error_msg() . PHP_EOL . 'data: ' . $json);
				fclose($handle);
			}
			throw new \InvalidArgumentException(
				'json_decode error: ' . json_last_error_msg());
		}
		return $data;
	}

	/**
	 * Wrapper for JSON encoding that throws when an error occurs.
	 *
	 * @param mixed $value   The value being encoded
	 * @param int    $options JSON encode option bitmask
	 * @param int    $depth   Set the maximum depth. Must be greater than zero.
	 *
	 * @return string
	 * @throws \InvalidArgumentException if the JSON cannot be encoded.
	 * @link http://www.php.net/manual/en/function.json-encode.php
	 */
	public static function json_encode($value, $options = 0, $depth = 512)
	{
		$json = \json_encode($value, $options, $depth);
		if (JSON_ERROR_NONE !== json_last_error()) {
			$DS 			=	DIRECTORY_SEPARATOR;
			$tempDir 		= 	__DIR__.	"{$DS}data{$DS}temp";
			$outputLogFile 	=	$tempDir .	"{$DS}json-last-errors.txt";

			$handle = fopen($outputLogFile, 'w+');
			if ($handle) {
				fwrite($handle, 'json_decode error: ' . json_last_error_msg() . PHP_EOL . 'data: ' . $value);
				fclose($handle);
			}
			throw new \InvalidArgumentException(
				'json_encode error: ' . json_last_error_msg());
		}

		return $json;
	}
}