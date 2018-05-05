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
* A stack of timings, it fetches a starting time at the moment the object is created.
*
* @package Papaya-Library
* @subpackage Profiler
*/
class PapayaProfilerTimer extends \PapayaObject implements \IteratorAggregate {

  private $_start = 0;

  private $_takes = array();

  /**
   * Store current microtime
   */
  public function __construct() {
    $this->_start = microtime(TRUE);
  }

  /**
   * take new timing. If you provide an value in $parameters
   * sprintf() or vsprintf() will be used to compile the text for
   * the timing.
   *
   * @param $text
   * @param null $parameters
   */
  public function take($text, $parameters = NULL) {
    $this->_takes[] = array(
      'time' => microtime(TRUE),
      'text' => array($text, $parameters)
    );
  }

  /**
   * Compile an return the list of taken timings.
   *
   * @return \Traversable
   */
  public function getIterator() {
    $result = array();
    $offset = $this->_start;
    foreach ($this->_takes as $take) {
      if (isset($take['text'][1]) && is_array($take['text'][1])) {
        $text = vsprintf($take['text'][0], $take['text'][1]);
      } elseif (isset($take['text'][1])) {
        $text = sprintf($take['text'][0], $take['text'][1]);
      } else {
        $text = $take['text'][0];
      }
      $result[] = array(
        'time' => $take['time'] - $offset,
        'time_string' => \PapayaUtilDate::periodToString($take['time'] - $offset),
        'start' => $offset,
        'end' => $offset = $take['time'],
        'text' => $text
      );
    }
    return new \ArrayIterator($result);
  }

  /**
   * Emit the timing to the message system as debug log messages
   */
  public function emit() {
    foreach ($this as $take) {
      $this->papaya()->messages->log(
        \PapayaMessageLogable::GROUP_DEBUG,
        \PapayaMessage::SEVERITY_DEBUG,
        $take['text'],
        new \PapayaMessageContextRuntime($take['start'], $take['end'])
      );
    }
  }
}
