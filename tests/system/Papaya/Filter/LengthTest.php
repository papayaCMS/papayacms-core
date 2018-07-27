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

class PapayaFilterLengthTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\Length::__construct
  */
  public function testConstructor() {
    $filter = new \Papaya\Filter\Length();
    $this->assertAttributeSame(
      0, '_minimum', $filter
    );
    $this->assertAttributeSame(
      NULL, '_maximum', $filter
    );
    $this->assertAttributeSame(
      FALSE, '_isUtf8', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Length::__construct
  */
  public function testConstructorWithAllArguments() {
    $filter = new \Papaya\Filter\Length(21, 42, TRUE);
    $this->assertAttributeSame(
      21, '_minimum', $filter
    );
    $this->assertAttributeSame(
      42, '_maximum', $filter
    );
    $this->assertAttributeSame(
      TRUE, '_isUtf8', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Length::__construct
  */
  public function testConstructorWithMaximumToSmallExpectingException() {
    $this->expectException(RangeException::class);
    new \Papaya\Filter\Length(4, 2);
  }

  /**
   * @covers \Papaya\Filter\Length::validate
   * @dataProvider provideValidValidateData
   * @param mixed $value
   * @param int|0 $minimum
   * @param int|NULL $maximum
   * @param bool $isUtf8
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateWithLimitsExpectingTrue(
    $value, $minimum, $maximum = NULL, $isUtf8 = FALSE
  ) {
    $filter = new \Papaya\Filter\Length($minimum, $maximum, $isUtf8);
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers \Papaya\Filter\Length::validate
   * @dataProvider provideInvalidValidateData
   * @param mixed $value
   * @param int|0 $minimum
   * @param int|NULL $maximum
   * @param bool $isUtf8
   * @throws \Papaya\Filter\Exception
   */
  public function testValidateWithLimitsExpectingException(
    $value, $minimum, $maximum = NULL, $isUtf8 = FALSE
  ) {
    $filter = new \Papaya\Filter\Length($minimum, $maximum, $isUtf8);
    $this->expectException(\Papaya\Filter\Exception::class);
    $filter->validate($value);
  }

  /**
  * @covers \Papaya\Filter\Length::validate
  */
  public function testValidateWithoutRange() {
    $filter = new \Papaya\Filter\Length();
    $this->assertTrue($filter->validate(42));
  }

  /**
  * @covers \Papaya\Filter\Length::validate
  */
  public function testValidateWithValueToShortExpectingException() {
    $filter = new \Papaya\Filter\Length(21, 42);
    $this->expectException(\Papaya\Filter\Exception\InvalidLength\ToShort::class);
    $filter->validate('foo');
  }

  /**
  * @covers \Papaya\Filter\Length::validate
  */
  public function testValidateWithValueToLongExpectingException() {
    $filter = new \Papaya\Filter\Length(0, 1);
    $this->expectException(\Papaya\Filter\Exception\InvalidLength\ToLong::class);
    $filter->validate('foo');
  }

  /**
  * @covers \Papaya\Filter\Length::filter
  */
  public function testFilter() {
    $filter = new \Papaya\Filter\Length(0, 10);
    $this->assertEquals('foo', $filter->filter('foo'));
  }

  /**
  * @covers \Papaya\Filter\Length::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \Papaya\Filter\Length(0, 1);
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
