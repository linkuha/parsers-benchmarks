<?php

  namespace Xparse\RecursivePagination;


  use Xparse\ElementFinder\ElementFinder;
  use Xparse\Parser\Parser;

  /**
   *
   * @package Xparse\RecursivePagination
   */
  class RecursivePagination {

    /**
     * @var array
     */
    protected $queue = [];

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $elementSelector = [];


    /**
     * @param Parser $parser
     * @param string|array $expression
     * @throws \Exception
     */
    public function __construct(Parser $parser, $expression) {
      $this->parser = $parser;

      $this->setExpression($expression);
    }


    /**
     * @param array|string $links
     * @param bool $state
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function addToQueue($links, $state = false) {
      if (!is_string($links) and !is_array($links)) {
        throw new \InvalidArgumentException('Links should be an array or a string');
      }
      foreach ((array) $links as $url) {
        if (!is_string($url)) {
          throw new \InvalidArgumentException('url should be a string');
        }
        $this->queue[$url] = $state;
      }

      return $this;
    }


    /**
     * @return ElementFinder|null
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function getNextPage() {

      $link = array_search(false, $this->queue, true);
      if ($link === false) {
        return null;
      }

      $this->queue[$link] = true;
      $page = $this->parser->get($link);

      if ($page === null) {
        return null;
      }

      foreach ($this->elementSelector as $expression => $state) {
        $queueLinks = $page->value($expression)->getItems();
        $countQueueLinks = count($queueLinks);
        if ($countQueueLinks > 0) {
          $queueLinks = array_combine($queueLinks, array_fill(0, $countQueueLinks, false));
          $this->queue = array_merge($queueLinks, $this->queue);
        }
      }

      return $page;
    }


    /**
     * @param string|array $expression
     * @throws \InvalidArgumentException
     */
    private function setExpression($expression) {

      if (!is_string($expression) and !is_array($expression)) {
        throw new \InvalidArgumentException('Invalid expression, should be array or string');
      }

      $expression = array_filter((array) $expression);

      if (count($expression) === 0) {
        throw new \InvalidArgumentException('Expression might be not empty');
      }

      foreach ($expression as $path) {
        if (!is_string($path)) {
          throw new \InvalidArgumentException('Invalid expression, should be a string');
        }
        $this->elementSelector[$path] = true;
      }
    }

  }