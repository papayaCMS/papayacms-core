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

class FloatValueTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\FloatValue::__construct
   */
  public function testConstructWithoutParams() {
    $testObj = new FloatValue();
    $this->assertAttributeEquals(NULL, '_min', $testObj);
    $this->assertAttributeEquals(NULL, '_max', $testObj);
  }

  /**
   * @covers \Papaya\Filter\FloatValue::__construct
   */
  public function testConstructWithoutFirstParam() {
    $min = -120;
    $testObj = new FloatValue($min);
    $this->assertAttributeEquals($min, '_min', $testObj);
    $this->assertAttributeEquals(NULL, '_max', $testObj);
  }

  /**
   * @covers \Papaya\Filter\FloatValue::__construct
   */
  public function testConstructWithoutWithBothParams() {
    $min = -120;
    $max = 120;
    $testObj = new FloatValue($min, $max);
    $this->assertAttributeEquals($min, '_min', $testObj);
    $this->assertAttributeEquals($max, '_max', $testObj);
  }

  /**
   * @covers \Papaya\Filter\FloatValue::validate
   */
  public function testValidate() {
    $filter = new FloatValue();
    $this->expectException(Exception\NotNumeric::class);
    $filter->validate('sgs');
  }

  /**
   * @covers \Papaya\Filter\FloatValue::validate
   */
  public function testValidateWithMinimum() {
    $filter = new FloatValue(-20.0);
    $this->expectException(Exception\OutOfRange\ToSmall::class);
    $filter->validate('-40');
  }

  /**
   * @covers \Papaya\Filter\FloatValue::validate
   */
  public function testValidateWithMinimumAndMaximum() {
    $filter = new FloatValue(-20.0, 40.5);
    $this->expectException(Exception\OutOfRange\ToLarge::class);
    $filter->validate('50');
  }

  /**
   * @covers \Papaya\Filter\FloatValue::validate
   */
  public function testValidateTrue() {
    $filter = new FloatValue(-20.0, 40.5);
    $this->assertTrue($filter->validate('10.51'));
  }

  /**
   * @covers       \Papaya\Filter\FloatValue::filter
   * @dataProvider provideValidFilterValues
   * @param float $expected
   * @param mixed $value
   * @param float $minimum
   * @param float $maximum
   */
  public function testFilterExpectingValue($expected, $value, $minimum, $maximum) {
    $filter = new FloatValue($minimum, $maximum);
    $this->assertEquals($expected, $filter->filter($value));
  }

  /**
   * @covers       \Papaya\Filter\FloatValue::filter
   * @dataProvider provideInvalidFilterValues
   * @param mixed $value
   * @param float $minimum
   * @param float $maximum
   */
  public function testFilterExpectingNull($value, $minimum, $maximum) {
    $filter = new FloatValue($minimum, $maximum);
    $this->assertNull($filter->filter($value));
  }

  public static function provideValidFilterValues() {
    return array(
      array(10.51, '10.51', NULL, NULL),
      array(0, 'abc', NULL, NULL),
      array(23.1, '23.10', 21, 42)
    );

  }

  public static function provideInvalidFilterValues() {
    return array(
      array('10', 11, 20),
      array('42', 11, 20)
    );
  }
}
