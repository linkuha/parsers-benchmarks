<?php

namespace parsing;

use GuzzleHttp\Psr7;


/**
 * For use in Graph
 */
class BasePage
{
	protected $id;

	protected $uri;

	protected $code;

//	protected $adjacency = array();

//	protected $anchors = array();

	/**
	 * BasePage constructor.
	 * @param Psr7\Uri $uri
	 * @param  int   $id
	 */
	public function __construct($id, Psr7\Uri $uri)
	{
		$this->uri = $uri;
		$this->id = $id;
	}

//	public function addAdj($id)
//	{
//		$this->adjacency[] = $id;
//	}

	public function getUriString()
	{
		return (string) $this->uri;
	}

	public function setCode($code)
	{
		$this->code = $code;
	}

	public function getUri()
	{
		return $this->uri;
	}
}