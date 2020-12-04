<?php

namespace HighchartsAdianti;

use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use HighchartsAdianti\HighchartOption;

/**
 * 
 * @author  Kásio Eduardo
 * @version 1.0, 2020-11-30
 */
class KGraficos extends TElement implements \ArrayAccess
{

  const HIGHCHART = 0;
  const ENGINE_JQUERY = 10;


  protected $value;
  protected $container;

  protected $_options = array();
  protected $_chartType;

  protected $_jsEngine;
  protected $_extraScripts = array();
  protected $_confs = array();

  /**
   * Class Constructor
   */
  public function __construct($chartType = self::HIGHCHART, $jsEngine = self::ENGINE_JQUERY)
  {
    parent::__construct('div');
    $this->container = new TElement('div');
    $this->container->id = 'containerChart';

    $this->_chartType = is_null($chartType) ? self::HIGHCHART : $chartType; //HIGHCHART
    $this->_jsEngine = is_null($jsEngine) ? self::ENGINE_JQUERY : $jsEngine; //JQUERY
    //Load default configurations
    //$this->setConfigurations();
  }

  /**
   * Finds the javascript files that need to be included on the page, based
   * on the chart type and js engine.
   * Uses the conf.php file to build the files path
   *
   * @return array The javascript files path
   */
  public function getScripts()
  {
    $scripts = array();
    switch ($this->_jsEngine) {

      case self::ENGINE_MOOTOOLS:
        $scripts[] = $this->_confs['mootools']['path'] . $this->_confs['mootools']['name'];
        if ($this->_chartType === self::HIGHCHART) {
          $scripts[] = $this->_confs['highchartsMootoolsAdapter']['path'] . $this->_confs['highchartsMootoolsAdapter']['name'];
        } else {
          $scripts[] = $this->_confs['highstockMootoolsAdapter']['path'] . $this->_confs['highstockMootoolsAdapter']['name'];
        }
        break;

      case self::ENGINE_PROTOTYPE:
        $scripts[] = $this->_confs['prototype']['path'] . $this->_confs['prototype']['name'];
        if ($this->_chartType === self::HIGHCHART) {
          $scripts[] = $this->_confs['highchartsPrototypeAdapter']['path'] . $this->_confs['highchartsPrototypeAdapter']['name'];
        } else {
          $scripts[] = $this->_confs['highstockPrototypeAdapter']['path'] . $this->_confs['highstockPrototypeAdapter']['name'];
        }
        break;
    }

    switch ($this->_chartType) {
      case self::HIGHCHART:
        $scripts[] = $this->_confs['highcharts']['path'] . $this->_confs['highcharts']['name'];
        break;

      case self::HIGHSTOCK:
        $scripts[] = $this->_confs['highstock']['path'] . $this->_confs['highstock']['name'];
        break;

      case self::HIGHMAPS:
        $scripts[] = $this->_confs['highmaps']['path'] . $this->_confs['highmaps']['name'];
        break;
    }

    //Include scripts with keys given to be included via includeExtraScripts
    if (!empty($this->_extraScripts)) {
      foreach ($this->_extraScripts as $key) {
        $scripts[] = $this->_confs['extra'][$key]['path'] . $this->_confs['extra'][$key]['name'];
      }
    }

    return $scripts;
  }




  /**
   * Prints javascript script tags for all scripts that need to be included on page
   *
   * @param boolean $return if true it returns the scripts rather then echoing them
   */
  private function printScripts()
  {
    TScript::importFromFile('lib/highcharts/highcharts.js');
  }

  /**
   * Manually adds an extra script to the extras
   *
   * @param string $key      key for the script in extra array
   * @param string $filepath path for the script file
   * @param string $filename filename for the script
   */
  public function addExtraScript($key, $filepath, $filename)
  {
    $this->_confs['extra'][$key] = array('name' => $filename, 'path' => $filepath);
  }


  /**
   * Signals which extra scripts are to be included given its keys
   *
   * @param array $keys extra scripts keys to be included
   */
  public function includeExtraScripts(array $keys = array())
  {
    $this->_extraScripts = empty($keys) ? array_keys($this->_confs['extra']) : $keys;
  }

  /**
   * Global options that don't apply to each chart like lang and global
   * must be set using the Highcharts.setOptions javascript method.
   * This method receives a set of HighchartOption and returns the
   * javascript string needed to set those options globally
   *
   * @param HighchartOption The options to create
   *
   * @return string The javascript needed to set the global options
   */
  public static function setOptions($options)
  {
    //TODO: Check encoding errors
    $option = json_encode($options->getValue());
    return "Highcharts.setOptions($option);";
  }

  /**
   * Render the chart options and returns the javascript that
   * represents them
   *
   * @return string The javascript code
   */
  public function renderOptions()
  {
    return HighchartOptionRenderer::render($this->_options);
  }


  public function __clone()
  {
    foreach ($this->_options as $key => $value) {
      $this->_options[$key] = clone $value;
    }
  }

  public function __set($offset, $value)
  {
    $this->offsetSet($offset, $value);
  }

  public function __get($offset)
  {
    return $this->offsetGet($offset);
  }

  public function offsetExists($offset)
  {
    return isset($this->_options[$offset]);
  }

  public function offsetUnset($offset)
  {
    unset($this->_options[$offset]);
  }

  public function offsetSet($offset, $value)
  {
    $this->_options[$offset] = new HighchartOption($value);
  }

  public function offsetGet($offset)
  {
    if (!isset($this->_options[$offset])) {
      $this->_options[$offset] = new HighchartOption();
    }
    return $this->_options[$offset];
  }

  /**
   * Shows the widget at the screen
   */
  public function show($varName = null, $callback = null, $withScriptTag = false)
  {
    TScript::importFromFile('https://code.highcharts.com/highcharts.js');

    $result = '';
    $result .= 'new Highcharts.';
    if ($this->_chartType === self::HIGHCHART) {
      $result .= 'Chart(';
    } else {
      $result .= 'StockChart(';
    }

    $result .= $this->renderOptions();
    $result .= is_null($callback) ? '' : ", $callback";
    $result .= ');';

    if ($withScriptTag) {
      $result = '<script type="text/javascript">' . $result . '</script>';
    }

    parent::add($this->container);
    TScript::create($result);
  }
}
