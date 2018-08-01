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

namespace Papaya\Message\Context;
/**
 * Message context containing the timing infotmations
 *
 * It is not possible to get the actual runtime of the php script, so all calls are relative to
 * the first instance of this class.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Runtime
  implements
  \Papaya\Message\Context\Interfaces\Text {

  /**
   * Global mode sets the timeing in relation to script runtime
   *
   * @var integer
   */
  const MODE_GLOBAL = 0;
  /**
   * Single mode just calculates and outputs a single timing,
   *
   * @var integer
   */
  const MODE_SINGLE = 1;

  /**
   * Defines if the timing is set in relation to the script runtime
   */
  private $_mode = self::MODE_GLOBAL;

  /**
   * Class variable to remember script start time
   *
   * @var integer
   */
  private static $_startTime = 0;

  /**
   * Class variable to remember last memory usage status and calculate differences
   *
   * @var integer
   */
  private static $_previousTime = 0;

  /**
   * Time value
   *
   * @var float
   */
  protected $_neededTime = 0;
  /**
   * Stop Time
   *
   * @var float
   */
  protected $_currentTime = 0;

  /**
   * Create object and set time property using start and stop time, setting no start value in
   * the constructor triggers the global mode.
   *
   * @see \PapayaMessageContextRuntime::setTimeValues
   *
   * @param NULL|float|string $start
   * @param NULL|float|string $stop
   */
  public function __construct($start = NULL, $stop = NULL) {
    if (self::$_previousTime == 0) {
      self::setStartTime(microtime(TRUE));
    }
    if (is_null($start)) {
      $stop = microtime(TRUE);
      $this->setTimeValues(
        self::$_previousTime,
        $stop
      );
      self::rememberTime($stop);
      $this->_mode = self::MODE_GLOBAL;
    } else {
      $this->setTimeValues(
        $start,
        is_null($stop) ? microtime(TRUE) : $stop
      );
      $this->_mode = self::MODE_SINGLE;
    }
  }

  /**
   * Get timing string
   */
  public function asString() {
    $result = '';
    switch ($this->_mode) {
      case self::MODE_GLOBAL :
        $timeFromStart = $this->_currentTime - self::$_startTime;
        $result .= 'Time: '.\Papaya\Utility\Date::periodToString($timeFromStart);
        $result .= ' (+'.\Papaya\Utility\Date::periodToString($this->_neededTime).')';
      break;
      case self::MODE_SINGLE :
        $result = 'Time needed: '.\Papaya\Utility\Date::periodToString($this->_neededTime);
      break;
    }
    return $result;
  }

  /**
   * Set time property using start and end time, strings are splitted at the first space, first
   * part considered milliseconds, second part seconds
   *
   * @param float|string $start
   * @param float|string $stop
   */
  public function setTimeValues($start, $stop) {
    $start = self::_prepareTimeValue($start);
    $stop = self::_prepareTimeValue($stop);
    $this->_currentTime = $stop;
    $this->_neededTime = $stop - $start;
  }

  /**
   * Remember stop time, for next timing
   *
   * @param integer $current
   */
  public static function rememberTime($current) {
    self::$_previousTime = $current;
  }

  /**
   * Prepare time value, strings are splitted at the first space, first
   * part considered milliseconds, second part seconds
   *
   * @param float|string $value
   * @return float
   */
  private static function _prepareTimeValue($value) {
    if (is_string($value) && strpos($value, ' ')) {
      list($milliSeconds, $seconds) = explode(' ', $value, 2);
      return ((float)$seconds + (float)$milliSeconds);
    } else {
      return (float)$value;
    }
  }

  /**
   * Initialize a start time.
   */
  public static function setStartTime($startTime) {
    self::$_previousTime = self::$_startTime = self::_prepareTimeValue($startTime);
  }
}
