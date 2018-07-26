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
   * @covers \PapayaFilterNumericWithUnit::__construct
   * @dataProvider providerConstructArguments
   * @param string|array $units
   * @param array $expectedUnits
   * @param float $minimum
   * @param float $maximum
   * @param string $algebraicSign
   */
  public function testConstruct($units, $expectedUnits, $minimum, $maximum, $algebraicSign) {
    $filter = new \PapayaFilterNumericWithUnit($units, $minimum, $maximum, $algebraicSign);
    $this->assertAttributeEquals($expectedUnits, '_units', $filter);
    $this->assertAttributeEquals($minimum, '_minimum', $filter);
    $this->assertAttributeEquals($maximum, '_maximum', $filter);
    $this->assertAttributeEquals($algebraicSign, '_algebraicSign', $filter);
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnit() {
    $filter = new \PapayaFilterNumericWithUnit('em');
    $this->assertAttributeEquals(array('em'), '_units', $filter);
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMin() {
    $filter = new \PapayaFilterNumericWithUnit('px', 5);
    $this->assertAttributeEquals(array('px'), '_units', $filter);
    $this->assertAttributeEquals(5, '_minimum', $filter);
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMinAndMax() {
    $filter = new \PapayaFilterNumericWithUnit(array('em', 'pt'), 81, 999, '-');
    $this->assertAttributeEquals(array('em', 'pt'), '_units', $filter);
    $this->assertAttributeEquals(81, '_minimum', $filter);
    $this->assertAttributeEquals(999, '_maximum', $filter);
    $this->assertAttributeEquals('-', '_algebraicSign', $filter);
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMinAndMaxAndAlgebraicSign() {
    $filter = new \PapayaFilterNumericWithUnit(array('%', 'pt'), -34, 91);
    $this->assertAttributeEquals(array('%', 'pt'), '_units', $filter);
    $this->assertAttributeEquals(-34, '_minimum', $filter);
    $this->assertAttributeEquals(91, '_maximum', $filter);
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructExceptionMissingUnit() {
    $this->expectException(UnexpectedValueException::class);
    new \PapayaFilterNumericWithUnit('');
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::getRegexpUnitOptions
  */
  public function testRegexpUnitOptions() {
    $expected = '(?:px|\?\+|\.\*)';
    $filter = new \PapayaFilterNumericWithUnit(array('px', '?+', '.*'));
    $this->assertSame($expected, $filter->getRegexpUnitOptions());
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidate() {
    $filter = new \PapayaFilterNumericWithUnit('px', 0, 100);
    $this->assertTrue($filter->validate('10px'));
  }


  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidateWithZero() {
    $filter = new \PapayaFilterNumericWithUnit('px', 0, 100);
    $this->assertTrue($filter->validate('0'));
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedFilterNotEnclosedException() {
    $filter = new \PapayaFilterNumericWithUnit('px');
    $this->expectException(\PapayaFilterExceptionNotEnclosed::class);
    $filter->validate('99abc');
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedCharacterInvalidExceptionNegativeValue() {
    $filter = new \PapayaFilterNumericWithUnit('em', 1, 1000, '-');
    $this->expectException(\PapayaFilterExceptionCharacterInvalid::class);
    $filter->validate('999em');
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedCharacterInvalidExceptionPositveValue() {
    $filter = new \PapayaFilterNumericWithUnit('em', 1, 1000, '+');
    $this->expectException(\PapayaFilterExceptionCharacterInvalid::class);
    $filter->validate('-999em');
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedFilterRangeMinimumException() {
    $filter = new \PapayaFilterNumericWithUnit('px', -10);
    $this->expectException(\PapayaFilterExceptionRangeMinimum::class);
    $filter->validate('-999px');
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedFilterRangeMaximumException() {
    $filter = new \PapayaFilterNumericWithUnit('px', -10, 999);
    $this->expectException(\PapayaFilterExceptionRangeMaximum::class);
    $filter->validate('1000px');
  }

  /**
   * @covers \PapayaFilterNumericWithUnit::filter
   * @dataProvider providerFilter
   * @param string|NULL $expects
   * @param mixed $value
   */
  public function testFilter($expects, $value) {
    $filter = new \PapayaFilterNumericWithUnit('px');
    $this->assertEquals($expects, $filter->filter($value));
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::filter
  */
  public function testFilterExpectsFilteredResult() {
    $filter = new \PapayaFilterNumericWithUnit('px');
    $this->assertEquals('102,324234.23px', $filter->filter(' 102,324234.23 px '));
  }

  /**
  * @covers \PapayaFilterNumericWithUnit::filter
  */
  public function testFilterExpectsNull() {
    $filter = new \PapayaFilterNumericWithUnit('px');
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
