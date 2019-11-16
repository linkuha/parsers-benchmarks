<?php

  namespace Xparse\ParserInterface;

  use Psr\Http\Message\RequestInterface;

  /**
   *
   * @author Ivan Scherbak <dev@funivan.com>
   * @package Xparse\ParserInterface
   */
  interface ParserInterface {

    /**
     * @param string $url
     * @return \Xparse\ElementFinder\ElementFinder
     */
    public function get($url);

    /**
     * @param string $url
     * @param array $data
     * @return \Xparse\ElementFinder\ElementFinder
     */
    public function post($url, $data);

    /**
     * @return \Xparse\ElementFinder\ElementFinder
     */
    public function getLastPage();

  }
