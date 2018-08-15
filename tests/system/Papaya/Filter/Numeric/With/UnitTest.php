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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterNumericWithUnitTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\NumberWithUnit::__construct
   * @dataProvider providerConstructArguments
   * @param string|array $units
   * @param array $expectedUnits
   * @param float $minimum
   * @param float $maximum
   * @param string $algebraicSign
   */
  public function testConstruct($units, $expectedUnits, $minimum, $maximum, $algebraicSign) {
    $filter = new \Papaya\Filter\NumberWithUnit($units, $minimum, $maximum, $algebraicSign);
    $this->assertAttributeEquals($expectedUnits, '_units', $filter);
    $this->assertAttributeEquals($minimum, '_minimum', $filter);
    $this->assertAttributeEquals($maximum, '_maximum', $filter);
    $this->assertAttributeEquals($algebraicSign, '_algebraicSign', $filter);
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::__construct
  */
  public function testConstructOnlyUnit() {
    $filter = new \Papaya\Filter\NumberWithUnit('em');
    $this->assertAttributeEquals(array('em'), '_units', $filter);
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMin() {
    $filter = new \Papaya\Filter\NumberWithUnit('px', 5);
    $this->assertAttributeEquals(array('px'), '_units', $filter);
    $this->assertAttributeEquals(5, '_minimum', $filter);
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMinAndMax() {
    $filter = new \Papaya\Filter\NumberWithUnit(array('em', 'pt'), 81, 999, '-');
    $this->assertAttributeEquals(array('em', 'pt'), '_units', $filter);
    $this->assertAttributeEquals(81, '_minimum', $filter);
    $this->assertAttributeEquals(999, '_maximum', $filter);
    $this->assertAttributeEquals('-', '_algebraicSign', $filter);
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMinAndMaxAndAlgebraicSign() {
    $filter = new \Papaya\Filter\NumberWithUnit(array('%', 'pt'), -34, 91);
    $this->assertAttributeEquals(array('%', 'pt'), '_units', $filter);
    $this->assertAttributeEquals(-34, '_minimum', $filter);
    $this->assertAttributeEquals(91, '_maximum', $filter);
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::__construct
  */
  public function testConstructExceptionMissingUnit() {
    $this->expectException(\UnexpectedValueException::class);
    new \Papaya\Filter\NumberWithUnit('');
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::getRegexpUnitOptions
  */
  public function testRegexpUnitOptions() {
    $expected = '(?:px|\?\+|\.\*)';
    $filter = new \Papaya\Filter\NumberWithUnit(array('px', '?+', '.*'));
    $this->assertSame($expected, $filter->getRegexpUnitOptions());
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidate() {
    $filter = new \Papaya\Filter\NumberWithUnit('px', 0, 100);
    $this->assertTrue($filter->validate('10px'));
  }


  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidateWithZero() {
    $filter = new \Papaya\Filter\NumberWithUnit('px', 0, 100);
    $this->assertTrue($filter->validate('0'));
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidateExpectedFilterNotEnclosedException() {
    $filter = new \Papaya\Filter\NumberWithUnit('px');
    $this->expectException(\Papaya\Filter\Exception\NotIncluded::class);
    $filter->validate('99abc');
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidateExpectedCharacterInvalidExceptionNegativeValue() {
    $filter = new \Papaya\Filter\NumberWithUnit('em', 1, 1000, '-');
    $this->expectException(\Papaya\Filter\Exception\InvalidCharacter::class);
    $filter->validate('999em');
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidateExpectedCharacterInvalidExceptionPositveValue() {
    $filter = new \Papaya\Filter\NumberWithUnit('em', 1, 1000, '+');
    $this->expectException(\Papaya\Filter\Exception\InvalidCharacter::class);
    $filter->validate('-999em');
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidateExpectedFilterRangeMinimumException() {
    $filter = new \Papaya\Filter\NumberWithUnit('px', -10);
    $this->expectException(\Papaya\Filter\Exception\OutOfRange\ToSmall::class);
    $filter->validate('-999px');
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::validate
  */
  public function testValidateExpectedFilterRangeMaximumException() {
    $filter = new \Papaya\Filter\NumberWithUnit('px', -10, 999);
    $this->expectException(\Papaya\Filter\Exception\OutOfRange\ToLarge::class);
    $filter->validate('1000px');
  }

  /**
   * @covers \Papaya\Filter\NumberWithUnit::filter
   * @dataProvider providerFilter
   * @param string|NULL $expects
   * @param mixed $value
   */
  public function testFilter($expects, $value) {
    $filter = new \Papaya\Filter\NumberWithUnit('px');
    $this->assertEquals($expects, $filter->filter($value));
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::filter
  */
  public function testFilterExpectsFilteredResult() {
    $filter = new \Papaya\Filter\NumberWithUnit('px');
    $this->assertEquals('102,324234.23px', $filter->filter(' 102,324234.23 px '));
  }

  /**
  * @covers \Papaya\Filter\NumberWithUnit::filter
  */
  public function testFilterExpectsNull() {
    $filter = new \Papaya\Filter\NumberWithUnit('px');
    $this->assertNull($filter->filter(' 102,324234dasda23px '));
  }

  /************************
  * Data Provider
  ************************/

  public static function providerConstructArguments() {
    return array(
      array('em', array('em'), -5, 82, NULL),
      array(array('pt', 'px'), array('pt', 'px'), 11, 965, '-'),
    );
  }

  public static function providerFilter() {
    return array(
      array('10px', 'ooijoijdiooi jgroj10iubweuifbiubuiwb fwiupxjnqjknjqwndjn   '),
      array('10,123.91px', 'ooijoijdiooi jgroj10,123.91iubweuifbiubuiwb fwiupxjnqjknjqwndjn   '),
      array('-82,992.93px', '    <b>-82,992.93px</b>'),
      array('992.93px', '    <b>992.93px</b>'),
      array('0', '0'),
    );
  }

}
