<?php

namespace helpers;

final class StringHelper
{
	/**
	 * Simple helper function for str encoding
	 *
	 * @param string $string
	 * @return string
	 */
	public static function safeEncodeStr($string) {
		return preg_replace_callback('/&#([a-z\d]+);/i', function ($m) {
			$value = (string) $m[0];
			$value = mb_convert_encoding($value, 'UTF-8', 'HTML-ENTITIES');
			return $value;
		}, $string);
	}

	public static function removeSpaceSymbols($string) {
		return preg_replace('/\s{1}/', '', $string);
	}

	public static function getUtf8Length($string) {
		if ('' === $string) return 0;

		if(extension_loaded('iconv')) {
			return iconv_strlen($string, 'utf-8');
		} elseif(extension_loaded('mbstring')) {
			return mb_strlen($string, 'utf-8');
		} elseif(extension_loaded('xml')) {
			return strlen(utf8_decode($string));
		} else {
			$count = count_chars($string);
			// 0x80 = 0x7F - 0 + 1 (one added to get inclusive range)
			// 0x33 = 0xF4 - 0x2C + 1 (one added to get inclusive range)
			return array_sum(array_slice($count, 0, 0x80)) +
			array_sum(array_slice($count, 0xC2, 0x33));
		}
	}

	/**
	 * Convert data from the given encoding to UTF-8.
	 *
	 * This has not yet been tested with charactersets other than UTF-8.
	 * It should work with ISO-8859-1/-13 and standard Latin Win charsets.
	 *
	 * @param string $data
	 *            The data to convert.
	 * @param string $encoding
	 *            A valid encoding. Examples: http://www.php.net/manual/en/mbstring.supported-encodings.php
	 */
	public static function convertToUTF8($data, $encoding = 'UTF-8')
	{
		/*
		 * From the HTML5 spec: Given an encoding, the bytes in the input stream must be converted to Unicode characters for the tokeniser, as described by the rules for that encoding, except that the leading U+FEFF BYTE ORDER MARK character, if any, must not be stripped by the encoding layer (it is stripped by the rule below). Bytes or sequences of bytes in the original byte stream that could not be converted to Unicode characters must be converted to U+FFFD REPLACEMENT CHARACTER code points.
		 */

		// mb_convert_encoding is chosen over iconv because of a bug. The best
		// details for the bug are on http://us1.php.net/manual/en/function.iconv.php#108643
		// which contains links to the actual but reports as well as work around
		// details.
		if (function_exists('mb_convert_encoding')) {
			// mb library has the following behaviors:
			// - UTF-16 surrogates result in false.
			// - Overlongs and outside Plane 16 result in empty strings.

			// Before we run mb_convert_encoding we need to tell it what to do with
			// characters it does not know. This could be different than the parent
			// application executing this library so we store the value, change it
			// to our needs, and then change it back when we are done. This feels
			// a little excessive and it would be great if there was a better way.
			$save = mb_substitute_character();
			mb_substitute_character('none');
			$data = mb_convert_encoding($data, 'UTF-8', $encoding);
			mb_substitute_character($save);
		}        // @todo Get iconv running in at least some environments if that is possible.
		elseif (function_exists('iconv') && $encoding != 'auto') {
			// fprintf(STDOUT, "iconv found\n");
			// iconv has the following behaviors:
			// - Overlong representations are ignored.
			// - Beyond Plane 16 is replaced with a lower char.
			// - Incomplete sequences generate a warning.
			$data = @iconv($encoding, 'UTF-8//IGNORE', $data);
		} else {
			// we can make a conforming native implementation
			throw new Exception('Not implemented, please install mbstring or iconv');
		}

		/*
		 * One leading U+FEFF BYTE ORDER MARK character must be ignored if any are present.
		 */
		if (substr($data, 0, 3) === "\xEF\xBB\xBF") {
			$data = substr($data, 3);
		}

		return $data;
	}
}