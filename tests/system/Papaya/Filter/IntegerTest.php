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

class PapayaFilterIntegerTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterInteger::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterInteger();
    $this->assertAttributeSame(
      NULL, '_minimum', $filter
    );
    $this->assertAttributeSame(
      NULL, '_maximum', $filter
    );
  }

  /**
  * @covers \PapayaFilterInteger::__construct
  */
  public function testConstructorWithMinimumAndMaximum() {
    $filter = new \PapayaFilterInteger(21, 42);
    $this->assertAttributeSame(
      21, '_minimum', $filter
    );
    $this->assertAttributeSame(
      42, '_maximum', $filter
    );
  }

  /**
  * @covers \PapayaFilterInteger::__construct
  */
  public function testConstructorWithMaximumOnlyExpectingException() {
    $this->expectException(RangeException::class);
    new \PapayaFilterInteger(NULL, 4);
  }

  /**
  * @covers \PapayaFilterInteger::__construct
  */
  public function testConstructorWithMaximumToSmallExpectingException() {
    $this->expectException(RangeException::class);
    new \PapayaFilterInteger(4, 2);
  }

  /**
   * @covers \PapayaFilterInteger::validate
   * @dataProvider provideValidValidateData
   * @param int $value
   * @param int $minimum
   * @param int $maximum
   * @throws \PapayaFilterException
   */
  public function testValidateWithLimitsExpectingTrue($value, $minimum, $maximum) {
    $filter = new \PapayaFilterInteger($minimum, $maximum);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \PapayaFilterInteger::validate
   * @dataProvider provideInvalidValidateData
   * @param int $value
   * @param int $minimum
   * @param int $maximum
   * @throws \PapayaFilterException
   */
  public function testValidateWithLimitsExpectingException($value, $minimum, $maximum) {
    $filter = new \PapayaFilterInteger($minimum, $maximum);
    $this->expectException(\PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
  * @covers \PapayaFilterInteger::validate
  */
  public function testValidateWithoutRange() {
    $filter = new \PapayaFilterInteger();
    $this->assertTrue($filter->validate(42));
  }

  /**
  * @covers \PapayaFilterInteger::validate
  */
  public function testValidateWithStringExpectingException() {
    $filter = new \PapayaFilterInteger();
    $this->expectException(\PapayaFilterException::class);
    $filter->validate('foo');
  }

  /**
  * @covers \PapayaFilterInteger::validate
  */
  public function testValidateWithFloatExpectingException() {
    $filter = new \PapayaFilterInteger();
    $this->expectException(\PapayaFilterException::class);
    $filter->validate(42.21);
  }

  /**
  * @covers \PapayaFilterInteger::validate
  */
  public function testValidateWithValueToSmallExpectingException() {
    $filter = new \PapayaFilterInteger(21, 42);
    $this->expectException(\Papaya\Filter\Exception\OutOfRange\ToSmall::class);
    $filter->validate(1);
  }

  /**
  * @covers \PapayaFilterInteger::validate
  */
  public function testValidateWithValueToLargeExpectingException() {
    $filter = new \PapayaFilterInteger(0, 1);
    $this->expectException(\Papaya\Filter\Exception\OutOfRange\ToLarge::class);
    $filter->validate(21);
  }

  /**
  * @covers \PapayaFilterInteger::filter
  */
  public function testFilter() {
    $filter = new \PapayaFilterInteger(0, 1);
    $this->assertEquals(1, $filter->filter(1));
  }

  /**
  * @covers \PapayaFilterInteger::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \PapayaFilterInteger(1);
    $this->assertNull($filter->filter('foo'));
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidValidateData() {
    return array(
      array('23', 21, 42),
      array('42', 21, NULL),
      array('-23', -42, 42),
      array('23', -42, 42),
      array('+23', -42, 42)
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array('10', 21, 42),
      array('42', 21, 23),
      array('-23', 0, 10),
      array('+23', -10, 0)
    );
  }
}
