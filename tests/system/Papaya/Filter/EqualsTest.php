<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterEqualsTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterEquals::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterEquals('success');
    $this->assertAttributeEquals(
      'success', '_value', $filter
    );
  }

  /**
  * @covers PapayaFilterEquals::validate
  * @dataProvider provideEqualValues
  */
  public function testValidate($expected, $value) {
    $filter = new PapayaFilterEquals($expected);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterEquals::validate
  * @dataProvider provideNonEqualValues
  */
  public function testValidateExpectingException($expected, $value) {
    $filter = new PapayaFilterEquals($expected);
    $this->setExpectedException(PapayaFilterExceptionNotEqual::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterEquals::filter
  * @dataProvider provideEqualValues
  */
  public function testFilter($expected, $value) {
    $filter = new PapayaFilterEquals($expected);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterEquals::filter
  * @dataProvider provideNonEqualValues
  */
  public function testFilterExpectingNull($expected, $value) {
    $filter = new PapayaFilterEquals($expected);
    $this->assertNull($filter->filter($value));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideEqualValues() {
    return array(
      array('true', 'true'),
      array(FALSE, FALSE),
      array(TRUE, TRUE),
      array(TRUE, 1),
      array(FALSE, 0),
      array(TRUE, 'true'),
      array(FALSE, '')
    );
  }

  public static function provideNonEqualValues() {
    return array(
      array('true', 'false'),
      array(TRUE, FALSE),
      array(FALSE, TRUE),
      array(TRUE, 0),
      array(FALSE, 1),
      array(FALSE, 'true'),
      array(TRUE, '')
    );
  }
}
