<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaFilterColorTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterColor::validate
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterColor();
    $this->assertTrue($filter->validate('#FFFFFF'));
  }

  /**
  * @covers PapayaFilterColor::validate
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterColor();
    $this->setExpectedException('PapayaFilterExceptionType');
    $filter->validate("invalid color");
  }

  /**
  * @covers PapayaFilterColor::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $input) {
    $filter = new PapayaFilterColor();
    $this->assertEquals($expected, $filter->filter($input));
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideFilterData() {
    return array(
      'valid' => array('#FFFFFF', "#FFFFFF"),
      'invalid string' => array(NULL, '#FF FF FF'),
      'invalid prefix' => array(NULL, 'FFFFFF'),
      'invalid length' => array(NULL, '#FFFFFFFFFF')
    );
  }
}
