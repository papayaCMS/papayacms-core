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

class PapayaFilterNumberTest extends PapayaTestCase {
  /**
  * @covers \PapayaFilterNumber::__construct
  */
  public function testConstructSuccess() {
    $filter = new \PapayaFilterNumber(15, 16);
    $this->assertAttributeEquals(15, '_minimumLength', $filter);
    $this->assertAttributeEquals(16, '_maximumLength', $filter);
  }

  /**
   * @covers \PapayaFilterNumber::__construct
   * @dataProvider constructFailureProvider
   * @param int $minimumLength
   * @param int $maximumLength
   */
  public function testConstructFailure($minimumLength, $maximumLength) {
    $this->expectException(UnexpectedValueException::class);
    new \PapayaFilterNumber($minimumLength, $maximumLength);
  }

  /**
   * @covers \PapayaFilterNumber::validate
   * @dataProvider validateSuccessProvider
   * @param mixed $value
   * @throws PapayaFilterExceptionRangeMaximum
   * @throws PapayaFilterExceptionRangeMinimum
   * @throws PapayaFilterExceptionType
   */
  public function testValidateSuccess($value) {
    $filter = new \PapayaFilterNumber(3, 4);
    /** @noinspection PhpUnhandledExceptionInspection */
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers \PapayaFilterNumber::validate
  */
  public function testValidateFailureFormat() {
    $filter = new \PapayaFilterNumber();
    $this->expectException(PapayaFilterExceptionType::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('I am not a number');
  }

  /**
  * @covers \PapayaFilterNumber::validate
  */
  public function testValidateFailureTooShort() {
    $filter = new \PapayaFilterNumber(3);
    $this->expectException(PapayaFilterExceptionRangeMinimum::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('22');
  }

  /**
  * @covers \PapayaFilterNumber::validate
  */
  public function testValidateFailureTooLong() {
    $filter = new \PapayaFilterNumber(NULL, 3);
    $this->expectException(PapayaFilterExceptionRangeMaximum::class);
    /** @noinspection PhpUnhandledExceptionInspection */
    $filter->validate('2222');
  }

  /**
   * @covers \PapayaFilterNumber::filter
   * @dataProvider filterSuccessProvider
   * @param mixed $value
   * @param mixed $filtered
   */
  public function testFilterSuccess($value, $filtered) {
    $filter = new \PapayaFilterNumber(3, 4);
    $this->assertEquals($filtered, $filter->filter($value));
  }

  /**
  * @covers \PapayaFilterNumber::filter
  */
  public function testFilterFailure() {
    $filter = new \PapayaFilterNumber();
    $this->assertNull($filter->filter('I am not a number'));
  }

  public static function constructFailureProvider() {
    return array(
      array(-1, NULL),
      array('String', NULL),
      array(NULL, -1),
      array(NULL, 'String'),
      array(5, 4)
    );
  }

  public static function validateSuccessProvider() {
    return array(
      array('100'),
      array('003'),
      array('0001')
    );
  }

  public static function filterSuccessProvider() {
    return array(
      array('0234', '0234'),
      array('    0234   ', '0234'),
      array('123', '123')
    );
  }
}
