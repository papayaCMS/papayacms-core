<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterBooleanStringTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterBooleanString
   * @dataProvider provideValidBooleanStrings
   */
  public function testValidateExpectingTrue($expected, $value) {
    $filter = new PapayaFilterBooleanString();
    $this->assertTrue($filter->validate($value));
  }

  /**
   * @covers PapayaFilterBooleanString
   * @dataProvider provideInvalidBooleanStrings
   */
  public function testValidateExpectingException($value) {
    $filter = new PapayaFilterBooleanString();
    $this->setExpectedException('PapayaFilterException');
    $filter->validate($value);
  }

  /**
   * @covers PapayaFilterBooleanString
   * @dataProvider provideValidBooleanStrings
   */
  public function testFilter($expected, $value) {
    $filter = new PapayaFilterBooleanString();
    $this->assertSame($expected, $filter->filter($value));
  }

  /**
   * @covers PapayaFilterBooleanString
   * @dataProvider provideInvalidBooleanStrings
   */
  public function testFilterExpectingNull($value) {
    $filter = new PapayaFilterBooleanString();
    $this->assertNull($filter->filter($value));
  }


  /**
   * @covers PapayaFilterBooleanString
   */
  public function testFilterWithoutCastingEmptyStringExpectingNull() {
    $filter = new PapayaFilterBooleanString(FALSE);
    $this->assertNull($filter->filter(''));
  }


  public static function provideValidBooleanStrings() {
    return array(
      array(TRUE, TRUE),
      array(TRUE, 1),
      array(TRUE, 42),
      array(TRUE, 'yes'),
      array(TRUE, 'YES'),
      array(TRUE, 'Yes'),
      array(TRUE, 'true'),
      array(TRUE, 'TRUE'),
      array(TRUE, 'True'),
      array(TRUE, 'TruE'),
      array(TRUE, 'abc'),
      array(TRUE, '012'),
      array(TRUE, '0ab'),
      array(FALSE, FALSE),
      array(FALSE, 0),
      array(FALSE, 'no'),
      array(FALSE, 'NO'),
      array(FALSE, 'No'),
      array(FALSE, 'false'),
      array(FALSE, 'FALSE'),
      array(FALSE, 'False'),
      array(FALSE, '0'),
      array(FALSE, ''),
      array(FALSE, '   ')
    );
  }

  public static function provideInvalidBooleanStrings() {
    return array(
      array(NULL)
    );
  }
}