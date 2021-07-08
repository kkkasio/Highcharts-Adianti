<?php

namespace Adianti\Plugins\Highcharts;

class HighchartJsExpr
{
  /**
   * The javascript expression
   *
   * @var string
   */
  private $_expression;

  /**
   * The HighchartJsExpr constructor
   *
   * @param string $expression The javascript expression
   */
  public function __construct($expression)
  {
    $this->_expression = iconv(
      mb_detect_encoding($expression),
      "UTF-8",
      $expression
    );
  }

  /**
   * Returns the javascript expression
   *
   * @return string The javascript expression
   */
  public function getExpression()
  {
    return $this->_expression;
  }
}
