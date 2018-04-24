<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterLengthTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterLength::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterLength();
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
  * @covers PapayaFilterLength::__construct
  */
  public function testConstructorWithAllArguments() {
    $filter = new PapayaFilterLength(21, 42, TRUE);
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
  * @covers PapayaFilterLength::__construct
  */
  public function testConstructorWithMaximumToSmallExpectingException() {
    $this->expectException(RangeException::class);
    $filter = new PapayaFilterLength(4, 2);
  }

  /**
  * @covers PapayaFilterLength::validate
  * @dataProvider provideValidValidateData
  */
  public function testValidateWithLimitsExpectingTrue(
    $value, $minimum, $maximum = TRUE, $isUtf8 = FALSE
  ) {
    $filter = new PapayaFilterLength($minimum, $maximum, $isUtf8);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterLength::validate
  * @dataProvider provideInvalidValidateData
  */
  public function testValidateWithLimitsExpectingException(
    $value, $minimum, $maximum = TRUE, $isUtf8 = FALSE
  ) {
    $filter = new PapayaFilterLength($minimum, $maximum, $isUtf8);
    $this->expectException(PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterLength::validate
  */
  public function testValidateWithoutRange() {
    $filter = new PapayaFilterLength();
    $this->assertTrue($filter->validate(42));
  }

  /**
  * @covers PapayaFilterLength::validate
  */
  public function testValidateWithValueToShortExpectingException() {
    $filter = new PapayaFilterLength(21, 42);
    $this->expectException(PapayaFilterExceptionLengthMinimum::class);
    $filter->validate('foo');
  }

  /**
  * @covers PapayaFilterLength::validate
  */
  public function testValidateWithValueToLongExpectingException() {
    $filter = new PapayaFilterLength(0, 1);
    $this->expectException(PapayaFilterExceptionLengthMaximum::class);
    $filter->validate('foo');
  }

  /**
  * @covers PapayaFilterLength::filter
  */
  public function testFilter() {
    $filter = new PapayaFilterLength(0, 10);
    $this->assertEquals('foo', $filter->filter('foo'));
  }

  /**
  * @covers PapayaFilterLength::filter
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterLength(0, 1);
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
