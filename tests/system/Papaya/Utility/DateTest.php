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

namespace Papaya\Utility;
require_once __DIR__.'/../../../bootstrap.php';

class DateTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Utility\Date::stringToTimestamp
   */
  public function testStringToTimestamp() {
    $this->assertEquals(
      '2010-12-18 14:37:42',
      date('Y-m-d H:i:s', Date::stringToTimestamp('2010-12-18 14:37:42'))
    );
  }

  /**
   * @covers \Papaya\Utility\Date::stringToTimestamp
   */
  public function testStringToTimestampExpectingFalse() {
    $this->assertFalse(
      Date::stringToTimestamp('')
    );
  }

  /**
   * @covers \Papaya\Utility\Date::stringToISO
   * @dataProvider stringToIsoDataProvider
   * @param string $dateString
   * @param bool $includeTime
   * @param string $expected
   */
  public function testStringToISO($dateString, $includeTime, $expected) {
    $this->assertEquals(
      $expected,
      Date::stringToISO($dateString, $includeTime)
    );
  }

  /**
   * @covers \Papaya\Utility\Date::stringToArray
   * @covers \Papaya\Utility\Date::_getValueFromArray
   * @dataProvider stringToArrayDataProvider
   * @param string $dateString
   * @param array|FALSE $expected
   */
  public function testStringToArray($dateString, $expected) {
    $this->assertSame($expected, Date::stringToArray($dateString));
  }

  /**
   * @covers \Papaya\Utility\Date::timestampToString
   *
   * @dataProvider timestampToStringDataProvider
   * @param int $timestamp
   * @param string $timezone
   * @param string $expected
   */
  public function testTimestampToString($timestamp, $timezone, $expected) {
    date_default_timezone_set($timezone);
    $this->assertEquals($expected, Date::timestampToString($timestamp));
  }

  /**
   * @covers \Papaya\Utility\Date::timestampToString
   */
  public function testTimestampToStringWithoutSeconds() {
    date_default_timezone_set('UTC');
    $this->assertEquals(
      '2010-04-01 11:31', Date::timestampToString(1270121471, FALSE, TRUE, TRUE)
    );
  }

  /**
   * @covers \Papaya\Utility\Date::timestampToString
   */
  public function testTimestampToStringWithoutOffset() {
    date_default_timezone_set('UTC');
    $this->assertEquals(
      '2010-04-01 11:31:11', Date::timestampToString(1270121471, TRUE, FALSE, TRUE)
    );
  }

  /**
   * @covers \Papaya\Utility\Date::timestampToString
   */
  public function testTimestampToStringWithoutWeekday() {
    date_default_timezone_set('UTC');
    $this->assertEquals(
      '2010-04-01 11:31:11+0000', Date::timestampToString(1270121471, TRUE, TRUE, FALSE)
    );
  }

  /**
   * @covers \Papaya\Utility\Date::periodToArray
   */
  public function testPeriodToArray() {
    $this->assertEquals(
      array(
        'y' => 21,
        'w' => 42,
        'd' => 3,
        'h' => 13,
        'min' => 31,
        's' => 11,
        'ms' => 220
      ),
      Date::periodToArray(
        self::getTimePeriodFixture(21, 42, 3, 13, 31, 11.22)
      )
    );
  }

  /**
   * @covers \Papaya\Utility\Date::periodToString
   * @covers \Papaya\Utility\Date::_roundPeriodElement
   * @dataProvider periodToArrayDataProvider
   * @param string $expected
   * @param string $period
   * @param int $precision
   */
  public function testPeriodToString($expected, $period, $precision) {
    $this->assertEquals(
      $expected,
      Date::periodToString($period, $precision)
    );
  }

  /**
   * @covers \Papaya\Utility\Date::periodToString
   * @covers \Papaya\Utility\Date::_roundPeriodElement
   */
  public function testPeriodToStringWithUnits() {
    $this->assertEquals(
      '21 Jahr(e) 42 Woche(n) 4 Tag(e)',
      Date::periodToString(
        self::getTimePeriodFixture(21, 42, 3, 13, 31, 11.22),
        3,
        array(
          'y' => ' Jahr(e)',
          'w' => ' Woche(n)',
          'd' => ' Tag(e)'
        )
      )
    );
  }

  /**
   * @covers \Papaya\Utility\Date::iso8601ToTimestamp
   * @dataProvider iso8601ToTimestampDataProvider
   * @param $expected
   * @param $datetime
   */
  public function testIso8601ToTimestamp($expected, $datetime) {
    $this->assertEquals($expected, Date::iso8601ToTimestamp($datetime));
  }

  /*************************************
   * Data Provider
   *************************************/

  public static function stringToIsoDataProvider() {
    return array(
      array('', FALSE, FALSE),
      array('2010-12-18 14:37:42', TRUE, '2010-12-18 14:37:42'),
      array('2010-12-18 14:37', TRUE, '2010-12-18 14:37:00'),
      array('2010-12-18 14', TRUE, '2010-12-18 14:00:00'),
      array('2010-12-18', TRUE, '2010-12-18 00:00:00'),
      array('2010-12-18 14:37:42', FALSE, '2010-12-18')
    );
  }

  public static function stringToArrayDataProvider() {
    return array(
      'empty' => array(
        NULL,
        FALSE
      ),
      'ISO date' => array(
        '2010-12-18',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 0,
          'minute' => 0,
          'second' => 0,
          'millisecond' => 0
        )
      ),
      'ISO date and time' => array(
        '2010-12-18 14:37',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 0,
          'millisecond' => 0
        )
      ),
      'ISO date and time with seconds' => array(
        '2010-12-18 14:37:42',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 42,
          'millisecond' => 0
        )
      ),
      'ISO date and time with seconds and milliseconds' => array(
        '2010-12-18 14:37:42.555',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 42,
          'millisecond' => 555
        )
      ),
      'German date' => array(
        '18.12.2010',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 0,
          'minute' => 0,
          'second' => 0,
          'millisecond' => 0
        )
      ),
      'German date and time' => array(
        '18.12.2010 14:37',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 0,
          'millisecond' => 0
        )
      ),
      'German date and time with seconds' => array(
        '18.12.2010 14:37:42',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 42,
          'millisecond' => 0
        )
      ),
      'English date' => array(
        '12/18/2010',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 0,
          'minute' => 0,
          'second' => 0,
          'millisecond' => 0
        )
      ),
      'English date and time' => array(
        '12/18/2010 14:37',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 0,
          'millisecond' => 0
        )
      ),
      'English date and time with seconds' => array(
        '12/18/2010 14:37:42',
        array(
          'year' => 2010,
          'month' => 12,
          'day' => 18,
          'hour' => 14,
          'minute' => 37,
          'second' => 42,
          'millisecond' => 0
        )
      )
    );
  }

  public static function timestampToStringDataProvider() {
    return array(
      array(0, 'UTC', '1970-01-01 00:00:00+0000 Thu'),
      array(0, 'Europe/Berlin', '1970-01-01 01:00:00+0100 Thu'),
      array(1, 'UTC', '1970-01-01 00:00:01+0000 Thu'),
      array(1, 'Europe/Berlin', '1970-01-01 01:00:01+0100 Thu'),
      array(1270121471, 'UTC', '2010-04-01 11:31:11+0000 Thu'),
      array(1270121471, 'Europe/Berlin', '2010-04-01 13:31:11+0200 Thu'),
      array(1271669349, 'UTC', '2010-04-19 09:29:09+0000 Mon'),
      array(1271669349, 'Europe/Berlin', '2010-04-19 11:29:09+0200 Mon')
    );
  }

  public static function periodToArrayDataProvider() {
    return array(
      array('0.55ms', self::getTimePeriodFixture(0, 0, 0, 0, 0, 0.000546), 2),
      array('2s 500ms', self::getTimePeriodFixture(0, 0, 0, 0, 0, 2.5), 2),
      array('3min 3s', self::getTimePeriodFixture(0, 0, 0, 0, 3, 2.5), 2),
      array('3min 2s 500ms', self::getTimePeriodFixture(0, 0, 0, 0, 3, 2.5), 3),
      array('4h 3min 3s', self::getTimePeriodFixture(0, 0, 0, 4, 3, 2.5), 3),
      array('5d 4h 3min', self::getTimePeriodFixture(0, 0, 5, 4, 3, 2.5), 3),
      array('6w 5d 4h', self::getTimePeriodFixture(0, 6, 5, 4, 3, 2.5), 3),
      array('7y 6w 5d', self::getTimePeriodFixture(7, 6, 5, 4, 3, 2.5), 3),
      array('8y', self::getTimePeriodFixture(7, 51, 0, 0, 0, 0), 1)
    );
  }

  public static function iso8601ToTimestampDataProvider() {
    return array(
      'empty' => array(
        NULL,
        FALSE
      ),
      'ISO format with positive hour offset' => array(
        1273587510,
        '2010-05-11T15:18:30+01'
      ),
      'ISO format with negative hour offset' => array(
        1273601910,
        '2010-05-11T17:18:30-01'
      ),
      'ISO format with positive hour & minute offset' => array(
        1273589310,
        '2010-05-11T15:18:30+01:30'
      ),
      'ISO format with negative hour & minute offset' => array(
        1273600110,
        '2010-05-11T17:18:30-01:30'
      ),
      'ISO format with positive hour offset and changing day' => array(
        1273616310,
        '2010-05-11T23:18:30+01:00'
      ),
      'ISO format with negative hour offset and changing day' => array(
        1273627110,
        '2010-05-12T00:18:30-01:00'
      ),
      'ISO format with milliseconds and without offset' => array(
        1273641510,
        '2010-05-12T05:18:30.100'
      )
    );
  }

  /*************************************
   * Fixtures
   *************************************/

  /**
   * Get time period in seconds
   *
   * @param integer $years
   * @param integer $weeks
   * @param integer $days
   * @param integer $hours
   * @param integer $minutes
   * @param float $seconds
   * @return float
   */
  public static function getTimePeriodFixture($years, $weeks, $days, $hours, $minutes, $seconds) {
    return (((($years * 52 + $weeks) * 7 + $days) * 24 + $hours) * 60 + $minutes) * 60 + $seconds;
  }
}

