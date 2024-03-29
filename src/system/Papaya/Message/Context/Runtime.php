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
 * Message context containing the timing information
 *
 * It is not possible to get the actual runtime of the php script, so all calls are relative to
 * the first instance of this class.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Runtime
  implements Interfaces\Text {
  /**
   * Global mode sets the timing in relation to script runtime
   *
   * @var int
   */
  const MODE_GLOBAL = 0;

  /**
   * Single mode just calculates and outputs a single timing,
   *
   * @var int
   */
  const MODE_SINGLE = 1;

  /**
   * Defines if the timing is set in relation to the script runtime
   */
  private $_mode = self::MODE_GLOBAL;

  /**
   * Class variable to remember script start time
   *
   * @var int
   */
  private static $_startTime = 0;

  /**
   * Class variable to remember last memory usage status and calculate differences
   *
   * @var int|null
   */
  private static $_previousTime;

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
   * @see \Papaya\Message\Context\Runtime::setTimeValues()
   *
   * @param null|float|string $start
   * @param null|float|string $stop
   */
  public function __construct($start = NULL, $stop = NULL) {
    if (NULL === self::$_previousTime) {
      self::setStartTime(\microtime(TRUE));
    }
    if (NULL === $start) {
      $stop = \microtime(TRUE);
      $this->setTimeValues(
        self::$_previousTime,
        $stop
      );
      self::rememberTime($stop);
      $this->_mode = self::MODE_GLOBAL;
    } else {
      $this->setTimeValues(
        $start,
        NULL === $stop ? \microtime(TRUE) : $stop
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
   * @param int $current
   */
  public static function rememberTime($current) {
    self::$_previousTime = $current;
  }

  /**
   * Prepare time value, strings are splitted at the first space, first
   * part considered milliseconds, second part seconds
   *
   * @param float|string $value
   *
   * @return float
   */
  private static function _prepareTimeValue($value) {
    if (\is_string($value) && \strpos($value, ' ')) {
      list($milliSeconds, $seconds) = \explode(' ', $value, 2);
      return ((float)$seconds + (float)$milliSeconds);
    }
    return (float)$value;
  }

  /**
   * Initialize a start time.
   *
   * @param float|null $startTime
   */
  public static function setStartTime($startTime) {
    self::$_previousTime = self::$_startTime = NULL !== $startTime ? self::_prepareTimeValue($startTime) : NULL;
  }

  public static function getStartTime(): float {
    return self::$_startTime;
  }
}
