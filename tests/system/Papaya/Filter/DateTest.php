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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterDateTest extends \PapayaTestCase {
  /**
  * @covers \PapayaFilterDate::__construct
  */
  public function testConstructSuccess() {
    $filter = new \PapayaFilterDate(\PapayaFilterDate::DATE_OPTIONAL_TIME, 600.0);
    $this->assertAttributeEquals(\PapayaFilterDate::DATE_OPTIONAL_TIME, '_includeTime', $filter);
    $this->assertAttributeEquals(600.0, '_step', $filter);
  }

  /**
  * @covers \PapayaFilterDate::__construct
  */
  public function testConstructExpectsExceptionIncludeTime() {
    $this->expectException(UnexpectedValueException::class);
    new \PapayaFilterDate(1000);
  }

  /**
  * @covers \PapayaFilterDate::__construct
  */
  public function testConstructExpectsExceptionStep() {
    $this->expectException(UnexpectedValueException::class);
    new \PapayaFilterDate(\PapayaFilterDate::DATE_OPTIONAL_TIME, -1);
  }

  /**
   * @covers \PapayaFilterDate::validate
   * @dataProvider validateSuccessProvider
   * @param int $includeTime
   * @param mixed $value
   * @throws \Papaya\Filter\Exception\OutOfRange\ToLarge
   * @throws \Papaya\Filter\Exception\UnexpectedType
   */
  public function testValidateSuccess($includeTime, $value) {
    $filter = new \PapayaFilterDate($includeTime);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \PapayaFilterDate::validate
   * @dataProvider validateExceptionFormatProvider
   * @param int $includeTime
   * @param mixed $value
   * @throws \Papaya\Filter\Exception\OutOfRange\ToLarge
   * @throws \Papaya\Filter\Exception\UnexpectedType
   */
  public function testValidateExceptionFormat($includeTime, $value) {
    $filter = new \PapayaFilterDate($includeTime);
    $this->expectException(\Papaya\Filter\Exception\UnexpectedType::class);
    $filter->validate($value);
  }

  /**
   * @covers \PapayaFilterDate::validate
   * @dataProvider validateExceptionRangeProvider
   * @param mixed $value
   * @throws \Papaya\Filter\Exception\OutOfRange\ToLarge
   * @throws \Papaya\Filter\Exception\UnexpectedType
   */
  public function testValidateExceptionRange($value) {
    $filter = new \PapayaFilterDate(\PapayaFilterDate::DATE_NO_TIME);
    $this->expectException(\Papaya\Filter\Exception\OutOfRange\ToLarge::class);
    $filter->validate($value);
  }

  /**
   * @covers \PapayaFilterDate::filter
   * @dataProvider filterSuccessProvider
   * @param mixed $value
   * @param string $result
   */
  public function testFilterSuccess($value, $result) {
    $filter = new \PapayaFilterDate(\PapayaFilterDate::DATE_OPTIONAL_TIME);
    $this->assertEquals($result, $filter->filter($value));
  }

  /**
  * @covers \PapayaFilterDate::filter
  */
  public function testFilterFailure() {
    $filter = new \PapayaFilterDate(\PapayaFilterDate::DATE_OPTIONAL_TIME);
    $this->assertNull($filter->filter('I am not a date'));
  }

  public static function validateSuccessProvider() {
    return array(
      array(\PapayaFilterDate::DATE_NO_TIME, '2010-02-28'),
      array(\PapayaFilterDate::DATE_NO_TIME, '2012-02-29'),
      array(\PapayaFilterDate::DATE_OPTIONAL_TIME, '2011-08-12'),
      array(\PapayaFilterDate::DATE_OPTIONAL_TIME, '2011-08-12 18:11'),
      array(\PapayaFilterDate::DATE_MANDATORY_TIME, '2011-08-12 18:11'),
      array(\PapayaFilterDate::DATE_MANDATORY_TIME, '2013-04-15T04:41:59.44Z')
    );
  }

  public static function validateExceptionFormatProvider() {
    return array(
      array(\PapayaFilterDate::DATE_NO_TIME, '11-08-12'),
      array(\PapayaFilterDate::DATE_NO_TIME, '2011-08'),
      array(\PapayaFilterDate::DATE_NO_TIME, '2011|08|12'),
      array(\PapayaFilterDate::DATE_NO_TIME, 'I am not a date'),
      array(\PapayaFilterDate::DATE_NO_TIME, '2011-08-12 18:36'),
      array(\PapayaFilterDate::DATE_OPTIONAL_TIME, '2011-08-12 18:36 garbage'),
      array(\PapayaFilterDate::DATE_MANDATORY_TIME, '2011-08-12')
    );
  }

  public static function validateExceptionRangeProvider() {
    return array(
      array('2011-02-29'),
      array('2012-02-30'),
      array('2011-08-32'),
      array('2011-13-01')
    );
  }

  public static function filterSuccessProvider() {
    return array(
      array('2011-08-12', '2011-08-12'),
      array('2011-08-12 18:53', '2011-08-12 18:53'),
      array('  2011-08-12  ', '2011-08-12')
    );
  }
}
