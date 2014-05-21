<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterNoLinebreakTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterNoLinebreak::validate
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterNoLinebreak();
    $this->assertTrue($filter->validate('Some Text Without Linebreak'));
  }

  /**
  * @covers PapayaFilterNoLinebreak::validate
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterNoLinebreak();
    $this->setExpectedException('PapayaFilterExceptionCharacterInvalid');
    $filter->validate("Two\r\nLines");
  }

  /**
  * @covers PapayaFilterNoLinebreak::filter
  * @dataProvider provideFilterData
  */
  public function testFilter($expected, $input) {
    $filter = new PapayaFilterNoLinebreak();
    $this->assertEquals(
      $expected, $filter->filter($input)
    );
  }

  /**********************
  * Data Provider
  **********************/

  public static function provideFilterData() {
    return array(
      '1 line' => array('Line', "Line"),
      '2 lines' => array('Line One Line Two', "Line One\r\nLine Two"),
      '3 lines' => array('Line One Line Two Line Three', "Line One\r\nLine Two\r\nLine Three"),
      'spaces' => array('Line One Line Two', "Line One   \r\n   Line Two")
    );
  }
}