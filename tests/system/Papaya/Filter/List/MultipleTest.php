<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterListMultipleTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterListMultiple::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterListMultiple(array(21, 42));
    $this->assertAttributeSame(
      array(21, 42), '_list', $filter
    );
  }

  /**
  * @covers PapayaFilterListMultiple::validate
  * @dataProvider provideValidValidateData
  */
  public function testValidateExpectingTrue($value, $validValues) {
    $filter = new PapayaFilterListMultiple($validValues);
    $this->assertTrue($filter->validate($value));
  }

  /**
  * @covers PapayaFilterListMultiple::validate
  * @dataProvider provideInvalidValidateData
  */
  public function testValidateExpectingException($value, $validValues) {
    $filter = new PapayaFilterListMultiple($validValues);
    $this->setExpectedException('PapayaFilterException');
    $filter->validate($value);
  }

  /**
  * @covers PapayaFilterListMultiple::filter
  * @dataProvider provideValidFilterData
  */
  public function testFilter($expected, $value, $validValues) {
    $filter = new PapayaFilterListMultiple($validValues);
    $this->assertSame($expected, $filter->filter($value));
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidValidateData() {
    return array(
      array(array('21'), array(21, 42)),
      array(array('21'), array('21', '42')),
      array(array('21', 42), array('21', '42')),
    );
  }

  public static function provideInvalidValidateData() {
    return array(
      array(array('23'), array(21, 42)),
      array(array('21', 23), array(21, 42)),
      array('string', array(21, 42)),
    );
  }

  public static function provideValidFilterData() {
    return array(
      array(array(21), array('21'), array(21, 42)),
      array(array(21), array('21', '23'), array(21, 42)),
      array(array(21, 42), array('21', '42'), array(21, 42)),
      array(array('21'), array('21'), array('21', '42')),
    );
  }
}