<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterListTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterList::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterList(array(21, 42));
    $this->assertAttributeEquals(
      array(21, 42), '_list', $filter
    );
  }
  /**
  * @covers PapayaFilterList::__construct
  */
  public function testConstructorWithTraversable() {
    $filter = new PapayaFilterList($iterator = new ArrayIterator(array(21, 42)));
    $this->assertAttributeSame(
      $iterator, '_list', $filter
    );
  }

  /**
  * @covers PapayaFilterList::validate
  * @dataProvider provideValidValidateData
  */
  public function testValidateExpectingTrue($value, $validValues) {
    $filter = new PapayaFilterList($validValues);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterList::validate
  * @dataProvider provideInvalidValidateData
  */
  public function testValidateExpectingException($value, $validValues) {
    $filter = new PapayaFilterList($validValues);
    $this->setExpectedException(PapayaFilterException::class);
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterList::filter
  * @dataProvider provideValidFilterData
  */
  public function testFilter($expected, $value, $validValues) {
    $filter = new PapayaFilterList($validValues);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterList::filter
  * @dataProvider provideInvalidValidateData
  */
  public function testFilterExpectingNull($value, $validValues) {
    $filter = new PapayaFilterList($validValues);
    $this->assertNull($filter->filter($value));
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidValidateData() {
    return array(
      array('21', array(21, 42)),
      array('21', array('21', '42')),
      array('21', new ArrayIterator(array('21', '42'))),
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array('23', array(21, 42)),
      array('23', new ArrayIterator(array('21', '42'))),
      array('', array(21, 42)),
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(21, '21', array(21, 42)),
      array('21', '21', array('21', '42')),
      array(21, '21', new ArrayIterator(array(21, 42))),
    );
  }
}
