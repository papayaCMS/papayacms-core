<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaFilterNumericWithUnitTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterNumericWithUnit::__construct
  * @dataProvider providerConstructArguments
  */
  public function testConstruct($units, $expectedUnits, $minimum, $maximum, $algebraicSign) {
    $filter = new PapayaFilterNumericWithUnit($units, $minimum, $maximum, $algebraicSign);
    $this->assertAttributeEquals($expectedUnits, '_units', $filter);
    $this->assertAttributeEquals($minimum, '_minimum', $filter);
    $this->assertAttributeEquals($maximum, '_maximum', $filter);
    $this->assertAttributeEquals($algebraicSign, '_algebraicSign', $filter);
  }

  /**
  * @covers PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnit() {
    $filter = new PapayaFilterNumericWithUnit('em');
    $this->assertAttributeEquals(array('em'), '_units', $filter);
  }

  /**
  * @covers PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMin() {
    $filter = new PapayaFilterNumericWithUnit('px', 5);
    $this->assertAttributeEquals(array('px'), '_units', $filter);
    $this->assertAttributeEquals(5, '_minimum', $filter);
  }

  /**
  * @covers PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMinAndMax() {
    $filter = new PapayaFilterNumericWithUnit(array('em', 'pt'), 81, 999, '-');
    $this->assertAttributeEquals(array('em', 'pt'), '_units', $filter);
    $this->assertAttributeEquals(81, '_minimum', $filter);
    $this->assertAttributeEquals(999, '_maximum', $filter);
    $this->assertAttributeEquals('-', '_algebraicSign', $filter);
  }

  /**
  * @covers PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructOnlyUnitAndMinAndMaxAndAlgebraicSign() {
    $filter = new PapayaFilterNumericWithUnit(array('%', 'pt'), -34, 91);
    $this->assertAttributeEquals(array('%', 'pt'), '_units', $filter);
    $this->assertAttributeEquals(-34, '_minimum', $filter);
    $this->assertAttributeEquals(91, '_maximum', $filter);
  }

  /**
  * @covers PapayaFilterNumericWithUnit::__construct
  */
  public function testConstructExceptionMissingUnit() {
    $this->setExpectedException('UnexpectedValueException');
    $filter = new PapayaFilterNumericWithUnit('');
  }

  /**
  * @covers PapayaFilterNumericWithUnit::getRegexpUnitOptions
  */
  public function testRegexpUnitOptions() {
    $expected = '(?:px|\?\+|\.\*)';
    $filter = new PapayaFilterNumericWithUnit(array('px', '?+', '.*'));
    $this->assertSame($expected, $filter->getRegexpUnitOptions());
  }

  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidate() {
    $filter = new PapayaFilterNumericWithUnit('px', 0, 100);
    $this->assertTrue($filter->validate('10px'));
  }


  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidateWithZero() {
    $filter = new PapayaFilterNumericWithUnit('px', 0, 100);
    $this->assertTrue($filter->validate('0'));
  }

  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedFilterNotEnclosedException() {
    $filter = new PapayaFilterNumericWithUnit('px');
    $this->setExpectedException('PapayaFilterExceptionNotEnclosed');
    $filter->validate('99abc');
  }

  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedCharacterInvalidExceptionNegativeValue() {
    $filter = new PapayaFilterNumericWithUnit('em', 1, 1000, '-');
    $this->setExpectedException('PapayaFilterExceptionCharacterInvalid');
    $filter->validate('999em');
  }

  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedCharacterInvalidExceptionPositveValue() {
    $filter = new PapayaFilterNumericWithUnit('em', 1, 1000, '+');
    $this->setExpectedException('PapayaFilterExceptionCharacterInvalid');
    $filter->validate('-999em');
  }

  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedFilterRangeMinimumException() {
    $filter = new PapayaFilterNumericWithUnit('px', -10);
    $this->setExpectedException('PapayaFilterExceptionRangeMinimum');
    $filter->validate('-999px');
  }

  /**
  * @covers PapayaFilterNumericWithUnit::validate
  */
  public function testValidateExpectedFilterRangeMaximumException() {
    $filter = new PapayaFilterNumericWithUnit('px', -10, 999);
    $this->setExpectedException('PapayaFilterExceptionRangeMaximum');
    $filter->validate('1000px');
  }

  /**
  * @covers PapayaFilterNumericWithUnit::filter
  * @dataProvider providerFilter
  */
  public function testFilter($expects, $value) {
    $filter = new PapayaFilterNumericWithUnit('px');
    $this->assertEquals($expects, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterNumericWithUnit::filter
  */
  public function testFilterExpectsFilteredResult() {
    $filter = new PapayaFilterNumericWithUnit('px');
    $this->assertEquals('102,324234.23px', $filter->filter(' 102,324234.23 px '));
  }

  /**
  * @covers PapayaFilterNumericWithUnit::filter
  */
  public function testFilterExpectsNull() {
    $filter = new PapayaFilterNumericWithUnit('px');
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