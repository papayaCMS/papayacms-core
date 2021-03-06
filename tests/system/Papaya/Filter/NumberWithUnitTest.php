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

namespace Papaya\Filter {
  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Filter\NumberWithUnit
   */
  class NumberWithUnitTest extends \Papaya\TestCase {

    /**
     * @dataProvider providerConstructArguments
     * @param string|array $units
     * @param array $expectedUnits
     * @param float $minimum
     * @param float $maximum
     * @param string $algebraicSign
     */
    public function testConstruct($units, $expectedUnits, $minimum, $maximum, $algebraicSign) {
      $filter = new NumberWithUnit($units, $minimum, $maximum, $algebraicSign);
      $this->assertAttributeEquals($expectedUnits, '_units', $filter);
      $this->assertAttributeEquals($minimum, '_minimum', $filter);
      $this->assertAttributeEquals($maximum, '_maximum', $filter);
      $this->assertAttributeEquals($algebraicSign, '_algebraicSign', $filter);
    }

    public function testConstructOnlyUnit() {
      $filter = new NumberWithUnit('em');
      $this->assertAttributeEquals(['em'], '_units', $filter);
    }

    public function testConstructOnlyUnitAndMin() {
      $filter = new NumberWithUnit('px', 5);
      $this->assertAttributeEquals(['px'], '_units', $filter);
      $this->assertAttributeEquals(5, '_minimum', $filter);
    }

    public function testConstructOnlyUnitAndMinAndMax() {
      $filter = new NumberWithUnit(['em', 'pt'], 81, 999, '-');
      $this->assertAttributeEquals(['em', 'pt'], '_units', $filter);
      $this->assertAttributeEquals(81, '_minimum', $filter);
      $this->assertAttributeEquals(999, '_maximum', $filter);
      $this->assertAttributeEquals('-', '_algebraicSign', $filter);
    }

    public function testConstructOnlyUnitAndMinAndMaxAndAlgebraicSign() {
      $filter = new NumberWithUnit(['%', 'pt'], -34, 91);
      $this->assertAttributeEquals(['%', 'pt'], '_units', $filter);
      $this->assertAttributeEquals(-34, '_minimum', $filter);
      $this->assertAttributeEquals(91, '_maximum', $filter);
    }

    public function testConstructExceptionMissingUnit() {
      $this->expectException(\UnexpectedValueException::class);
      new NumberWithUnit('');
    }

    public function testValidate() {
      $filter = new NumberWithUnit('px', 0, 100);
      $this->assertTrue($filter->validate('10px'));
    }

    public function testValidateWithZero() {
      $filter = new NumberWithUnit('px', 0, 100);
      $this->assertTrue($filter->validate('0'));
    }

    public function testValidateExpectedFilterNotEnclosedException() {
      $filter = new NumberWithUnit('px');
      $this->expectException(Exception\NotIncluded::class);
      $filter->validate('99abc');
    }

    public function testValidateExpectedCharacterInvalidExceptionNegativeValue() {
      $filter = new NumberWithUnit('em', 1, 1000, '-');
      $this->expectException(Exception\InvalidCharacter::class);
      $filter->validate('999em');
    }

    public function testValidateExpectedCharacterInvalidExceptionPositiveValue() {
      $filter = new NumberWithUnit('em', 1, 1000, '+');
      $this->expectException(Exception\InvalidCharacter::class);
      $filter->validate('-999em');
    }

    public function testValidateExpectedFilterRangeMinimumException() {
      $filter = new NumberWithUnit('px', -10);
      $this->expectException(Exception\OutOfRange\ToSmall::class);
      $filter->validate('-999px');
    }

    public function testValidateExpectedFilterRangeMaximumException() {
      $filter = new NumberWithUnit('px', -10, 999);
      $this->expectException(Exception\OutOfRange\ToLarge::class);
      $filter->validate('1000px');
    }

    /**
     * @dataProvider providerFilter
     * @param string|NULL $expects
     * @param mixed $value
     */
    public function testFilter($expects, $value) {
      $filter = new NumberWithUnit('px');
      $this->assertEquals($expects, $filter->filter($value));
    }

    public function testFilterExpectsFilteredResult() {
      $filter = new NumberWithUnit('px');
      $this->assertEquals('102,324234.23px', $filter->filter(' 102,324234.23 px '));
    }

    public function testFilterExpectsNull() {
      $filter = new NumberWithUnit('px');
      $this->assertNull($filter->filter(' 102,324234dasda23px '));
    }

    /************************
     * Data Provider
     ************************/

    public static function providerConstructArguments() {
      return [
        ['em', ['em'], -5, 82, NULL],
        [['pt', 'px'], ['pt', 'px'], 11, 965, '-'],
      ];
    }

    public static function providerFilter() {
      return [
        ['10px', 'ooijoijdiooi jgroj10iubweuifbiubuiwb fwiupxjnqjknjqwndjn   '],
        ['10,123.91px', 'ooijoijdiooi jgroj10,123.91iubweuifbiubuiwb fwiupxjnqjknjqwndjn   '],
        ['-82,992.93px', '    <b>-82,992.93px</b>'],
        ['992.93px', '    <b>992.93px</b>'],
        ['0', '0'],
      ];
    }

  }
}
