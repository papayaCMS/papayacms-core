<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterNotEmptyTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterNotEmpty::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterNotEmpty();
    $this->assertAttributeEquals(
      TRUE, '_ignoreSpaces', $filter
    );
  }

  /**
  * @covers PapayaFilterNotEmpty::__construct
  */
  public function testConstructorWithArguments() {
    $filter = new PapayaFilterNotEmpty(FALSE);
    $this->assertAttributeEquals(
      FALSE, '_ignoreSpaces', $filter
    );
  }

  /**
  * @covers PapayaFilterNotEmpty::validate
  * @dataProvider provideNonEmptyValues
  */
  public function testValidate($value, $ignoreSpaces) {
    $filter = new PapayaFilterNotEmpty($ignoreSpaces);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterNotEmpty::validate
  * @dataProvider provideEmptyValues
  */
  public function testValidateExpectingException($value, $ignoreSpaces) {
    $filter = new PapayaFilterNotEmpty($ignoreSpaces);
    $this->expectException(PapayaFilterExceptionEmpty::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterNotEmpty();
    $this->assertNull($filter->filter(''));
  }

  /**
  * @covers PapayaFilterNotEmpty::filter
  */
  public function testFilterWithEmptyArrayExpectingNull() {
    $filter = new PapayaFilterNotEmpty();
    $this->assertNull($filter->filter(array()));
  }

  /**
  * @covers PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingValue() {
    $filter = new PapayaFilterNotEmpty();
    $this->assertEquals('some', $filter->filter('some'));
  }

  /**
  * @covers PapayaFilterNotEmpty::filter
  */
  public function testFilterWithArrayExpectingValue() {
    $filter = new PapayaFilterNotEmpty();
    $this->assertEquals(array('some'), $filter->filter(array('some')));
  }

  /**
  * @covers PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingTrimmedValue() {
    $filter = new PapayaFilterNotEmpty();
    $this->assertEquals('some', $filter->filter(' some '));
  }

  /**
  * @covers PapayaFilterNotEmpty::filter
  */
  public function testFilterExpectingWhitespaceValue() {
    $filter = new PapayaFilterNotEmpty(FALSE);
    $this->assertEquals(' ', $filter->filter(' '));
  }

  /************************
  * Data Provider
  ************************/

  public static function provideNonEmptyValues() {
    return array(
      array('some', FALSE),
      array('some', TRUE),
      array(' ', FALSE),
      array(array('some'), FALSE)
    );
  }

  public static function provideEmptyValues() {
    return array(
      array('', TRUE),
      array('', FALSE),
      array(' ', TRUE),
      array(array(), TRUE)
    );
  }
}
