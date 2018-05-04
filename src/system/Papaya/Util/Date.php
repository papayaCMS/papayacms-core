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
* Papaya Utilities for Date and Time
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilDate {

  /**
  * parses a user input datetime string with offset to get a unix timestamp.
  *
  * @param string $datetime a datetime string Y-m-dTH:i:s[+-[H[:i]]]
  * @return mixed timestamp if $date was matched, otherwise FALSE
  */
  public static function iso8601ToTimestamp($datetime) {
    $result = FALSE;
    $patternDateISO = '(^
      (?P<year>[0-9]{4})[-:](?P<month>[0-9]{2})[-:](?P<day>[0-9]{2})
      (?:[T ](?P<hour>[0-9]{1,2})
        (?::(?P<minute>[0-9]{1,2})
          (?::(?P<second>[0-9]{1,2})
            (?:\.(?P<millisecond>[0-9]{1,3}))?
            (?:(?P<offsetOperator>[+-])
              (?:(?P<offsetHour>[0-9]{2})
                (?::?(?P<offsetMinute>[0-9]{2})?
                  (?::(?P<offsetSecond>[0-9]{2}))?
                )?
              )?
            )?
          )?
        )?
      )?
      $)Dx';
    if (preg_match($patternDateISO, $datetime, $matches)) {
      $resultMatch = array(
        'year' => (int)self::_getValueFromArray($matches, 'year', date('Y')),
        'month' => (int)self::_getValueFromArray($matches, 'month', 1),
        'day' => (int)self::_getValueFromArray($matches, 'day', 1),
        'hour' => (int)self::_getValueFromArray($matches, 'hour', 0),
        'minute' => (int)self::_getValueFromArray($matches, 'minute', 0),
        'second' => (int)self::_getValueFromArray($matches, 'second', 0),
        'millisecond' => (int)self::_getValueFromArray($matches, 'millisecond', 0)
      );
      if (isset($matches['offsetOperator']) && !empty($matches['offsetOperator']) &&
          isset($matches['offsetHour']) && (int)($matches['offsetHour']) > 0) {
        if ($matches['offsetOperator'] == '+') {
          $resultMatch['hour'] -= (int)$matches['offsetHour'];
          if (isset($matches['offsetMinute']) && (int)($matches['offsetMinute']) > 0) {
            $resultMatch['minute'] += (int)$matches['offsetMinute'];
          }
        } else {
          $resultMatch['hour'] += (int)$matches['offsetHour'];
          if (isset($matches['offsetMinute']) && (int)($matches['offsetMinute']) > 0) {
            $resultMatch['minute'] -= (int)$matches['offsetMinute'];
          }
        }
      }
      $result = gmmktime(
        $resultMatch['hour'],
        $resultMatch['minute'],
        $resultMatch['second'],
        $resultMatch['month'],
        $resultMatch['day'],
        $resultMatch['year']
      );
    }
    return $result;
  }

  /**
  * parses a user input date string to get a unix timestamp
  *
  * @param string $date a date string d.m.Y H:i:s OR m/d/Y H:i:s OR Y-m-d H:i:s
  * @return int|FALSE timestamp if $date was matched, otherwise FALSE
  */
  public static function stringToTimestamp($date) {
    if ($array = self::stringToArray($date)) {
      return mktime(
        $array['hour'],
        $array['minute'],
        $array['second'],
        $array['month'],
        $array['day'],
        $array['year']
      );
    }
    return FALSE;
  }

  /**
   * parses a user input date string to get an iso date/time string
   *
   * @param string $date a date string d.m.Y H:i:s OR m/d/Y H:i:s OR Y-m-d H:i:s
   * @param bool $includeTime
   * @return string iso date time
   */
  public static function stringToISO($date, $includeTime = TRUE) {
    if ($array = self::stringToArray($date)) {
      $result = str_pad($array['year'], 4, '0', STR_PAD_LEFT);
      $result .= '-'.str_pad($array['month'], 2, '0', STR_PAD_LEFT);
      $result .= '-'.str_pad($array['day'], 2, '0', STR_PAD_LEFT);
      if ($includeTime) {
        $result .= ' '.str_pad($array['hour'], 2, '0', STR_PAD_LEFT);
        $result .= ':'.str_pad($array['minute'], 2, '0', STR_PAD_LEFT);
        $result .= ':'.str_pad($array['second'], 2, '0', STR_PAD_LEFT);
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * parses a user input date string to get a array with all parts
  *
  * @param string $date a date string d.m.Y H:i:s OR m/d/Y H:i:s OR Y-m-d H:i:s
  * @return mixed timestamp if $date was matched, otherwise FALSE
  */
  public static function stringToArray($date) {
    $patternDateDE = '(^
      (?P<day>[0-9]{1,2})\\.(?P<month>[0-9]{1,2})\\.(?P<year>[0-9]{4})
      (?:\s(?P<hour>[0-9]{1,2})
        (?::(?P<minute>[0-9]{1,2})
          (?::(?P<second>[0-9]{1,2}))?
        )?
      )?
      $)Dx';
    $patternDateEN = '(
      ^(?P<month>[0-9]{1,2})/(?P<day>[0-9]{1,2})/(?P<year>[0-9]{4})
      (?:\s(?P<hour>[0-9]{1,2})
        (?::(?P<minute>[0-9]{1,2})
          (?::(?P<second>[0-9]{1,2}))?
        )?
      )?
      $)Dx';
    $patternDateISO = '(^
      (?P<year>[0-9]{4})[-:](?P<month>[0-9]{2})[-:](?P<day>[0-9]{2})
      (?:[T ](?P<hour>[0-9]{1,2})
        (?::(?P<minute>[0-9]{1,2})
          (?::(?P<second>[0-9]{1,2})
            (?:\.(?P<millisecond>[0-9]{1,3}))?
          )?
        )?
      )?
      $)Dx';
    if (preg_match($patternDateISO, $date, $matches) ||
        preg_match($patternDateEN, $date, $matches) ||
        preg_match($patternDateDE, $date, $matches)) {
      return array(
        'year' => (int)self::_getValueFromArray($matches, 'year', date('Y')),
        'month' => (int)self::_getValueFromArray($matches, 'month', 1),
        'day' => (int)self::_getValueFromArray($matches, 'day', 1),
        'hour' => (int)self::_getValueFromArray($matches, 'hour', 0),
        'minute' => (int)self::_getValueFromArray($matches, 'minute', 0),
        'second' => (int)self::_getValueFromArray($matches, 'second', 0),
        'millisecond' => (int)self::_getValueFromArray($matches, 'millisecond', 0)
      );
    }
    return FALSE;
  }

  /**
   * convert a unix timestamp to an string
   *
   * @param integer $timestamp
   * @param boolean $seconds
   * @param bool $offset only used if $seconds is TRUE
   * @param bool $weekDay
   * @return string
   */
  public static function timestampToString(
    $timestamp, $seconds = TRUE, $offset = TRUE, $weekDay = TRUE
  ) {
    $formatString = 'Y-m-d H:i';
    if ($seconds) {
      $formatString .= ':s';
      if ($offset) {
        $formatString .= 'O';
        if ($weekDay) {
          $formatString .= ' D';
        }
      }
    }
    return date($formatString, (int)$timestamp);
  }

  /**
   * Convert a time period (in seconds) to an array.
   *
   * @param $period
   * @return array
   * @internal param float|int $seconds
   */
  public static function periodToArray($period) {
    $seconds = floor($period);
    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $days = floor($hours / 24);
    $weeks = floor($days / 7);
    $years = floor($weeks / 52);
    return array(
      'y' => $years,
      'w' => $weeks - ($years * 52),
      'd' => $days - ($weeks * 7),
      'h' => $hours - ($days * 24),
      'min' => $minutes - ($hours * 60),
      's' => $seconds - ($minutes * 60),
      'ms' => round(($period - $seconds) * 1000, 2),
    );
  }

  /**
   * Generic formatting for a time period
   *
   * @uses \PapayaUtilDate::periodToArray()
   *
   * @param integer $period
   * @param integer $precision elements to output (start with the first one greater zero)
   * @param array $units map standard units to strings
   * @return string
   */
  public static function periodToString($period, $precision = 2, $units = array()) {
    $elements = self::periodToArray($period);
    $result = '';
    $counter = 0;
    $patterns = array(
      array('y', 'w', 25.5),
      array('w', 'd', 3.5),
      array('d', 'h', 12),
      array('h', 'min', 30),
      array('min', 's', 30),
      array('s', 'ms', 500),
    );
    foreach ($patterns as $pattern) {
      list($current, $next, $divider) = $pattern;
      $unit = isset($units[$current]) ? $units[$current] : $current;
      if ($counter + $elements[$current] > 0 &&
          $precision >= ++$counter) {
        $result .= ' ';
        if ($precision > $counter) {
          $result .= $elements[$current];
        } else {
          $result .= self::_roundPeriodElement($elements[$current], $elements[$next], $divider);
        }
        $result .= $unit;
      }
    }
    if ($precision > $counter) {
      $result .= ' '.$elements['ms'].'ms';
    }
    return substr($result, 1);
  }

  /**
   * Round period elements by the next element
   *
   * @param integer $value
   * @param integer $fragmentValue
   * @param float $divider
   * @return int
   */
  private static function _roundPeriodElement($value, $fragmentValue, $divider) {
    if ($fragmentValue >= $divider) {
      return $value + 1;
    } else {
      return $value;
    }
  }

  /**
  * Return the element from the array if it is founf and not empty, in other cases
  * return the default value.
  *
  * @param array $array
  * @param string $key
  * @param mixed $defaultValue
  * @return mixed
  */
  private static function _getValueFromArray(array $array, $key, $defaultValue) {
    return (empty($array[$key])) ? $defaultValue : $array[$key];
  }
}
