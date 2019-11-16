<?php

namespace parsing\wrappers;

use helpers\TidyHelper;

class Offer {

	private $id;
	private $href;

	public function setId($id) {
		$this->id .= $id;
	}

	public function setHref($href) {
		$this->href = $href;
	}

	public function getId() {
		return $this->id;
	}

	public function getHref() {
		return $this->href;
	}
}

/** The parser can't be configured to automatically call individual methods
 * for each specific tag; instead, you must handle this yourself.
*/
class ExpatParser
{
	const FUNC_FOPEN 	= 1;
	const FUNC_FILE		= 2;

	private $tag;
	private $item;

	private $data = array();

	public function getData() {
		return $this->data;
	}

	private function offerStart($parser, $tag, $attributes) {
		if ('offer' == $tag) {
			$this->item = new Offer();
			$this->item->setId($attributes['id']);
		} elseif ('url' == $tag) {
			$this->tag = $tag;
		}
	}

	private function offerData($parser, $data) {
		if ('url' == $this->tag && !empty($this->item)) {
			$this->item->setHref($data);
		}
	}

	private function offerEnd($parser, $tag) {
		if ('offer' == $tag) {
			$this->data[] = [
				'text' => $this->item->getId(),
				'href' => $this->item->getHref(),
			];
			unset($this->item);
		}
	}

	private function stringElement($parser, $str) {
		//todo ??
	}

	public static function parseLinks($content, $contentType = 'text/html', $inputEncoding = 'UTF-8') {
		$data = array();

		if ($contentType === 'text/xml') {
			$isXml = true;
			$xmlString = $content;
		} else {
			$isXml = false;
			$xmlString = TidyHelper::getCleanHtml5($content, $inputEncoding, 'text/xml');
		}
		$parser = xml_parser_create();

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, true);

		xml_parse_into_struct($parser, $xmlString, $values, $tags);
		if ($isXml) {
			if(!empty($tags['offer'])){
				foreach ($tags['offer'] as $key => $val) {
					$tag = $values[$val];
					$id = $tag['attributes']['id'];
					if ($id === null) continue;

					$data[] = [
						'text' => $id,
						'href' => "I can't :(",
					];
				}
			}
		} else {
			if(!empty($tags['a'])){
				foreach ($tags['a'] as $key => $val) {
					$tag = $values[$val];
					$href = $tag['attributes']['href'];
					if (empty($tag['value']) && empty($href)) continue;

					$data[] = [
						'text' => $tag['value'] ,
						'href' => $href,
					];
				}
			}
		}
		xml_parser_free($parser);
		return $data;
	}

	/**
	 * @param      $fileOrUrl
	 * @param int  $func
	 * @param bool $everychar
	 * @return array
	 * @throws \Exception
	 * @internal param string $file_or_url
	 * @internal param bool $funcFopen
	 */
	public function parseLinksStream($fileOrUrl, $func = self::FUNC_FOPEN, $everychar = false) {
		// 1 parameter: The supported output encodings are ISO-8859-1, UTF-8 and US-ASCII.
		// eq. option XML_OPTION_TARGET_ENCODING
		// default: UTF-8 since php 5.0.2
		$parser = xml_parser_create();

		xml_set_object($parser, $this);
		xml_set_element_handler($parser, 'offerStart', 'offerEnd');
		xml_set_character_data_handler($parser, 'offerData');
		// bring to a unified register
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, true);

		if (self::FUNC_FOPEN === $func) {
			$handle = fopen($fileOrUrl, 'r');
			if ($handle === false) {
				throw new \Exception("fopen() can't read file (ExpatParser)");
			}
			if ($everychar === false) {
				while ($data = fread($handle, 4096)) {
					if (!xml_parse($parser, $data, feof($handle))) {
						throw new \Exception(
							"XML Error: ".
							xml_error_string(xml_get_error_code($parser)).
							" at line ".xml_get_current_line_number($parser)
						);
					}
					//usleep(1000);
				}
			} else {
				$firstStep = true;
				$data = "";
				while (!feof($handle)) {
					$char = fgetc($handle);
					$data .= $char;
					if ($char != '>') { continue; }
					if ($firstStep) {
						$data = strstr($data, '<?');
						$firstStep = false;
					}
					if (!xml_parse($parser, $data, feof($handle))) {
						throw new \Exception(
							"XML Error: ".
							xml_error_string(xml_get_error_code($parser)).
							" at line ".xml_get_current_line_number($parser)
						);
					}
					$data = "";
					//usleep(1000);
				}
			}
			fclose($handle);
		}
		if (self::FUNC_FILE === $func) {
			$lines = file($fileOrUrl);
			if ($lines === false) {
				throw new \Exception("file() can't read file (ExpatParser)");
			}
			foreach ($lines as $line_num => $line) {
				if (!xml_parse($parser, rtrim($line, '\n'))) {
					throw new \Exception(
						"XML Error: ".
						xml_error_string(xml_get_error_code($parser)).
						" at line ".xml_get_current_line_number($parser)
					);
				}
				//usleep(1000);
			}
		}
		xml_parser_free($parser);
		return $this->getData();
	}
}