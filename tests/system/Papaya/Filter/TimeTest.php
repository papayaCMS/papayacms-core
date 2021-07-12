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

namespace Papaya\Filter;
require_once __DIR__.'/../../../bootstrap.php';

/**
 * @covers \Papaya\Filter\Time
 */
class TimeTest extends \Papaya\TestFramework\TestCase {

  public function testConstructFailure() {
    $this->expectException(\UnexpectedValueException::class);
    new Time(-1);
  }

  /**
   * @dataProvider validateSuccessProvider
   * @param mixed $timeString
   * @throws Exception\OutOfRange\ToLarge
   * @throws Exception\UnexpectedType
   */
  public function testValidateSuccess($timeString) {
    $filter = new Time(1);
    $this->assertTrue($filter->validate($timeString));
  }

  /**
   * @dataProvider validateExceptionTypeProvider
   * @param mixed $timeString
   * @throws Exception\OutOfRange\ToLarge
   * @throws Exception\UnexpectedType
   */
  public function testValidateExceptionType($timeString) {
    $filter = new Time();
    $this->expectException(Exception\UnexpectedType::class);
    $filter->validate($timeString);
  }

  /**
   * @dataProvider validateExceptionRangeMaximumProvider
   * @param mixed $timeString
   * @throws Exception\OutOfRange\ToLarge
   * @throws Exception\UnexpectedType
   */
  public function testValidateExceptionRangeMaximum($timeString) {
    $filter = new Time();
    $this->expectException(Exception\OutOfRange\ToLarge::class);
    $filter->validate($timeString);
  }

  public function testValidateExceptionTypeForStepMismatch() {
    $filter = new Time(1800);
    $this->expectException(Exception\UnexpectedType::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('17:45');
  }

  /**
   * @dataProvider filterProvider
   * @param mixed $timeString
   * @param string|NULL $expected
   */
  public function testFilter($timeString, $expected) {
    $filter = new Time();
    $this->assertEquals($expected, $filter->filter($timeString));
  }

  public static function validateSuccessProvider() {
    return array(
      array('00:00:00'),
      array('12:00'),
      array('19:57:21'),
      array('23:59'),
      array('11:31:23Z')
    );
  }

  public static function validateExceptionTypeProvider() {
    return array(
      array('I am not a valid time'),
      array(''),
      array('hh:mm:ss'),
      array('12_56_29'),
      array('11:31:23+02:00')
    );
  }

  public static function validateExceptionRangeMaximumProvider() {
    return array(
      array('25:11:21'),
      array('23:82:11'),
      array('11:45:99')
    );
  }

  public static function filterProvider() {
    return array(
      array('23:23', '23:23'),
      array('23:15:00   ', '23:15:00'),
      array('45:87:91', NULL),
      array('I am not a time', NULL)
    );
  }
}
