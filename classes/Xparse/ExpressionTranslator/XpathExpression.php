<?php

  namespace Xparse\ExpressionTranslator;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 02.12.15
   */
  class XpathExpression implements ExpressionTranslatorInterface {

    /**
     * @inheritdoc
     */
    public function convertToXpath($expression) {
      return $expression;
    }
    
  }