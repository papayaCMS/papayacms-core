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
* This filter class checks a date with optional time in human-readable format.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterDate implements \Papaya\Filter {
  /**
  * Do not include a time
  * @constant int
  */
  const DATE_NO_TIME = 0;

  /**
  * Optionally include a time
  * @constant int
  */
  const DATE_OPTIONAL_TIME = 1;

  /**
  * Include a mandatory time
  * @constant int
  */
  const DATE_MANDATORY_TIME = 2;

  /**
  * Static array of all time constants
  * @staticvar array
  */
  private static $timeConstants = array(
    self::DATE_NO_TIME,
    self::DATE_OPTIONAL_TIME,
    self::DATE_MANDATORY_TIME
  );

  /**
  * Include a time?
  * @var boolean
  */
  private $_includeTime = self::DATE_NO_TIME;

  /**
  * Step for the included time in seconds, default 60
  * @var float
  */
  private $_step = 1.0;

  /**
   * Constructor
   *
   * @param integer $includeTime optional, default self::DATE_NO_TIME
   * @param float $step optional, default 1.0
   * @throws \UnexpectedValueException
   */
  public function __construct($includeTime = self::DATE_NO_TIME, $step = 1.0) {
    if (!in_array($includeTime, self::$timeConstants)) {
      throw new \UnexpectedValueException(
        'Argument must be \PapayaFilterDate::DATE_NO_TIME, '.
        '\PapayaFilterDate::DATE_OPTIONAL_TIME, or '.
        '\PapayaFilterDate::DATE_MANDATORY_TIME.'
      );
    }
    if ($step <= 0) {
      throw new \UnexpectedValueException('Step must be greater than 0.');
    }
    $this->_includeTime = $includeTime;
    $this->_step = $step;
  }

  /**
   * Validate a date
   *
   * @param string $value
   * @throws \PapayaFilterExceptionType
   * @throws \Papaya\Filter\Exception\OutOfRange\ToLarge
   * @return boolean
   */
  public function validate($value) {
    if ($this->_includeTime > self::DATE_NO_TIME) {
      $elements = preg_split('([T ])', $value);
      if (count($elements) > 2 ||
          ($this->_includeTime == self::DATE_MANDATORY_TIME && count($elements) != 2)) {
        throw new \PapayaFilterExceptionType('Wrong number of elements in date/time string.');
      }
      $date = $elements[0];
      if (count($elements) > 1) {
        $time = $elements[1];
      }
    } else {
      $date = $value;
    }
    $patternDateISO = '(^
      (?P<year>\d{4})-
      (?P<month>\d{2})-
      (?P<day>\d{2})
    $)Dx';
    if (!preg_match($patternDateISO, $date, $matches)) {
      throw new \PapayaFilterExceptionType('Invalid date format.');
    }
    $daysPerMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $year = $matches['year'];
    $month = $matches['month'];
    $day = $matches['day'];
    if (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0) {
      $daysPerMonth[1] = 29;
    }
    if ($month > 12) {
      throw new \Papaya\Filter\Exception\OutOfRange\ToLarge(12, $month);
    }
    if ($day > $daysPerMonth[$month - 1]) {
      throw new \Papaya\Filter\Exception\OutOfRange\ToLarge($daysPerMonth[$month - 1], $day);
    }
    if (isset($time)) {
      $timeFilter = new \PapayaFilterTime($this->_step);
      $timeFilter->validate($time);
    }
    return TRUE;
  }

  /**
  * Filter a date
  *
  * @param string $value
  * @return mixed the filtered date value or NULL
  */
  public function filter($value) {
    try {
      $this->validate(trim($value));
    } catch(\PapayaFilterException $e) {
      return NULL;
    }
    return trim($value);
  }
}
