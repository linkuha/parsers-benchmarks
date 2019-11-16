<?php

  namespace Xparse\ExpressionTranslator;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 02.12.15
   */
  interface ExpressionTranslatorInterface {

    /**
     * Translate expression to xpath
     * For example you can use css
     *
     * @param string $expression
     * @return string
     */
    public function convertToXpath($expression);

  }