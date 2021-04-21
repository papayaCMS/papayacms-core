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
 * @covers \Papaya\Filter\Length
 */
class LengthTest extends \Papaya\TestCase {

  public function testConstructorWithMaximumToSmallExpectingException() {
    $this->expectException(\RangeException::class);
    new Length(4, 2);
  }

  /**
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param int|0 $minimum
   * @param int|NULL $maximum
   * @param bool $isUtf8
   * @throws Exception
   */
  public function testValidateWithLimitsExpectingTrue(
    $value, $minimum, $maximum = NULL, $isUtf8 = FALSE
  ) {
    $filter = new Length($minimum, $maximum, $isUtf8);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param int|0 $minimum
   * @param int|NULL $maximum
   * @param bool $isUtf8
   * @throws Exception
   */
  public function testValidateWithLimitsExpectingException(
    $value, $minimum, $maximum = NULL, $isUtf8 = FALSE
  ) {
    $filter = new Length($minimum, $maximum, $isUtf8);
    $this->expectException(Exception::class);
    $filter->validate($value);
  }

  public function testValidateWithoutRange() {
    $filter = new Length();
    $this->assertTrue($filter->validate(42));
  }

  public function testValidateWithValueToShortExpectingException() {
    $filter = new Length(21, 42);
    $this->expectException(Exception\InvalidLength\ToShort::class);
    $filter->validate('foo');
  }

  public function testValidateWithValueToLongExpectingException() {
    $filter = new Length(0, 1);
    $this->expectException(Exception\InvalidLength\ToLong::class);
    $filter->validate('foo');
  }

  public function testFilter() {
    $filter = new Length(0, 10);
    $this->assertEquals('foo', $filter->filter('foo'));
  }

  public function testFilterExpectingNull() {
    $filter = new Length(0, 1);
    $this->assertNull($filter->filter('foo'));
  }

  /**************************
   * Data Provider
   **************************/

  public static function provideValidValidateData() {
    return array(
      array('foo', 0, 20),
      array('foo', 1, NULL),
      array('foobar', 3, 10),
      array('äöü', 3, 3, TRUE)
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array('foo', 4),
      array('foo', 1, 2),
      array('äöü', 3, 3, FALSE)
    );
  }
}
