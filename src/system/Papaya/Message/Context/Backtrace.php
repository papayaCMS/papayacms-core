<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* Message string context containing a backtrace
*
* This class is used for debug and error mesages, to provide additional information about
* the callstack.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageContextBacktrace
  implements
    PapayaMessageContextInterfaceList,
    PapayaMessageContextInterfaceString,
    PapayaMessageContextInterfaceXhtml {

  /**
  * The offset is used to ignore the first elements of a backtrace,
  * if they do not provide useful informations
  *
  * @var integer
  */
  private $_offset = 0;

  /**
  * Backtrace data from debug_backtrace()
  *
  * @var array
  */
  private $_backtrace = NULL;

  /**
   * Create backtrace
   *
   * @param integer $offset ignore first several items of the backtrace
   * @param array $backtrace
   */
  public function __construct($offset = 0, array $backtrace = NULL) {
    if (isset($backtrace)) {
      $this->setBacktrace($backtrace, $offset);
    } else {
      $this->setOffset($offset + 1);
      $this->getBacktrace();
    }
  }

  /**
   * Check an set backtrace offset
   * @param integer $offset
   * @throws InvalidArgumentException
   */
  public function setOffset($offset) {
    if (!is_int($offset) || $offset < 0) {
      throw new \InvalidArgumentException('$offset must be an integer greater or equal zero.');
    }
    $this->_offset = $offset;
  }

  /**
  * Set backtrace
  *
  * @param array $backtrace
  * @param integer $offset
  */
  public function setBacktrace(array $backtrace, $offset = 0) {
    $this->_backtrace = $backtrace;
    $this->_offset = $offset;
  }

  /**
  * Get backtrace, create one if not yet stored in object property
  *
  * @return array
  */
  public function getBacktrace() {
    if (is_null($this->_backtrace)) {
      return $this->_backtrace = debug_backtrace();
    }
    $trace = $this->_backtrace;
    array_splice($trace, 0, $this->_offset);
    return $trace;
  }

  /**
  * Convert list to a string (with line breaks for each item)
  *
  * @return string
  */
  public function asString() {
    $list = $this->asArray();
    return implode("\n", $list);
  }

  /**
  * Convert list to a xhtml string (with breaks for each item)
  *
  * @return string
  */
  public function asXhtml() {
    $list = $this->asArray();
    $result = '';
    foreach ($list as $key => $element) {
      if ($key > 0) {
        $result .= "<br />\n";
      }
      $result .= PapayaUtilStringXml::escape($element);
    }
    return $result;
  }

  /**
  * Convert backtrace to a list of strings
  *
  * @return array
  */
  public function asArray() {
    $backtrace = $this->getBacktrace();
    $lines = array();
    foreach ($backtrace as $item) {
      $line = '';
      if (!empty($item['class'])) {
        $line .= $item['class'];
      }
      if (!empty($item['type'])) {
        $line .= $item['type'];
      }
      if (!empty($item['function'])) {
        $line .= $item['function'].'()';
      }
      if (!empty($item['file'])) {
        $line .= ' '.$item['file'];
        if (isset($item['line'])) {
          $line .= ':'.((int)$item['line']);
        }
      }
      $lines[] = $line;
    }
    return $lines;
  }

  /**
  * Provides a label/title for the context
  *
  * @return string
  */
  public function getLabel() {
    return 'Backtrace';
  }
}
