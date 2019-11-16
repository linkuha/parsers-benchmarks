<?php

namespace parsing\wrappers;

use parsing\SimpleXMLReader;

//header ("Content-type: text/html, charset=utf-8;");

class SimpleXMLReaderParser extends SimpleXMLReader
{
	protected $data = array();

    public function __construct()
    {
        // by node name or xpath (why don't work???)
        $this->registerCallback("offer", array($this, "callbackLinks"));
    }

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	protected function callbackLinks($reader)
    {
		$xml = $reader->expandSimpleXml();

		// with namespace
        //$attributes = $xml->attributes('rdf', true);
		$attributes = $xml->attributes();
		$id = (string) $attributes->{'id'};
		$url = $xml->xpath('/url');

		$this->data[] = [
			'text' => (string) $id,
			'href' => (string) $url->title,
		];
        return true;
    }

}


