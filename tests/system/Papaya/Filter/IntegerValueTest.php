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
 * @covers \Papaya\Filter\IntegerValue
 */
class IntegerValueTest extends \Papaya\TestFramework\TestCase {

  public function testConstructorWithMaximumOnlyExpectingException() {
    $this->expectException(\RangeException::class);
    new IntegerValue(NULL, 4);
  }

  public function testConstructorWithMaximumToSmallExpectingException() {
    $this->expectException(\RangeException::class);
    new IntegerValue(4, 2);
  }

  /**
   * @dataProvider provideValidValidateData
   * @param int $value
   * @param int $minimum
   * @param int $maximum
   * @throws Exception
   */
  public function testValidateWithLimitsExpectingTrue($value, $minimum, $maximum) {
    $filter = new IntegerValue($minimum, $maximum);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @dataProvider provideInvalidValidateData
   * @param int $value
   * @param int $minimum
   * @param int $maximum
   * @throws Exception
   */
  public function testValidateWithLimitsExpectingException($value, $minimum, $maximum) {
    $filter = new IntegerValue($minimum, $maximum);
    $this->expectException(Exception::class);
    $filter->validate($value);
  }

  public function testValidateWithoutRange() {
    $filter = new IntegerValue();
    $this->assertTrue($filter->validate(42));
  }

  public function testValidateWithStringExpectingException() {
    $filter = new IntegerValue();
    $this->expectException(Exception::class);
    $filter->validate('foo');
  }

  public function testValidateWithFloatExpectingException() {
    $filter = new IntegerValue();
    $this->expectException(Exception::class);
    $filter->validate(42.21);
  }

  public function testValidateWithValueToSmallExpectingException() {
    $filter = new IntegerValue(21, 42);
    $this->expectException(Exception\OutOfRange\ToSmall::class);
    $filter->validate(1);
  }

  public function testValidateWithValueToLargeExpectingException() {
    $filter = new IntegerValue(0, 1);
    $this->expectException(Exception\OutOfRange\ToLarge::class);
    $filter->validate(21);
  }

  public function testFilter() {
    $filter = new IntegerValue(0, 1);
    $this->assertEquals(1, $filter->filter(1));
  }

  public function testFilterExpectingNull() {
    $filter = new IntegerValue(1);
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
