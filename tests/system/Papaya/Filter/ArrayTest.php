<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaFilterArrayTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterArray::__construct
  */
  public function testConstructorWithElementFilter() {
    $filter = new PapayaFilterArray($subFilter = $this->getMock('PapayaFilter'));
    $this->assertAttributeSame(
      $subFilter, '_elementFilter', $filter
    );
  }

  /**
  * @covers PapayaFilterArray::validate
  * @dataProvider provideValidValidateData
  */
  public function testValidateExpectingTrue($value, $elementFilter = NULL) {
    $filter = new PapayaFilterArray($elementFilter);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterArray::validate
  * @dataProvider provideInvalidValidateData
  */
  public function testValidateExpectingException($value, $elementFilter = NULL) {
    $filter = new PapayaFilterArray($elementFilter);
    $this->setExpectedException('PapayaFilterException');
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterArray::filter
  * @dataProvider provideValidFilterData
  */
  public function testFilter($expected, $value, $elementFilter = NULL) {
    $filter = new PapayaFilterArray($elementFilter);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
  * @covers PapayaFilterArray::filter
  * @dataProvider provideInvalidFilterData
  */
  public function testFilterExpectingNull($value, $elementFilter = NULL) {
    $filter = new PapayaFilterArray($elementFilter);
    $this->assertNull($filter->filter($value));
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidValidateData() {
    return array(
      array(array('foo')),
      array(array('foo'), new PapayaFilterNotEmpty()),
      array(array('21', '42'), new PapayaFilterInteger())
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      'empty string' => array(''),
      'empty array' => array(array()),
      'scalar' => array('23'),
      'empty element' => array(array(''), new PapayaFilterNotEmpty()),
      'no integer element' => array(array('foo'), new PapayaFilterInteger())
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(array('foo'), array('foo')),
      array(array('foo'), array('foo'), new PapayaFilterNotEmpty()),
      array(array(21, 42), array('21', '42'), new PapayaFilterInteger())
    );
  }

  public static function provideInvalidFilterData() {
    return array(
      'empty string' => array(''),
      'empty array' => array(array()),
      'scalar' => array('23'),
      'empty element' => array(array(''), new PapayaFilterNotEmpty())
    );
  }
}
