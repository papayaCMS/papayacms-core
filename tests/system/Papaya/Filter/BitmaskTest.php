<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterBitmaskTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterBitmask::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterBitmask(array(1, 2, 4));
    $this->assertAttributeEquals(
      array(1, 2, 4), '_bits', $filter
    );
  }

  /**
  * @covers PapayaFilterBitmask::validate
  * @dataProvider provideValidBitmasks
  */
  public function testValidateExpectingTrue($bitmask) {
    $filter = new PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->assertTrue(
      $filter->validate($bitmask)
    );
  }

  /**
  * @covers PapayaFilterBitmask::validate
  * @dataProvider provideInvalidBitmasks
  */
  public function testValidateExpectingInvalidValueException($bitmask) {
    $filter = new PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->setExpectedException('PapayaFilterExceptionInvalid');
    $filter->validate($bitmask);
  }

  /**
  * @covers PapayaFilterBitmask::validate
  */
  public function testValidateExpectingInvalidValueTypeException() {
    $filter = new PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->setExpectedException('PapayaFilterExceptionType');
    $filter->validate('fail');
  }

  /**
  * @covers PapayaFilterBitmask::filter
  * @dataProvider provideValidBitmasks
  */
  public function testFilterWithValidBitmasks($bitmask) {
    $filter = new PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->assertEquals(
      $bitmask, $filter->filter($bitmask)
    );
  }

  /**
  * @covers PapayaFilterBitmask::filter
  * @dataProvider provideInvalidBitmasks
  */
  public function testFilterWithInvalidBitmasks($bitmask) {
    $filter = new PapayaFilterBitmask(array(1, 2, 4, 16));
    $this->assertNull(
      $filter->filter($bitmask)
    );
  }

  public static function provideValidBitmasks() {
    return array(
      array(0),
      array(1 | 2),
      array(1 | 16),
      array(1 | 2 | 4 | 16)
    );
  }
  public static function provideInvalidBitmasks() {
    return array(
      array(-1),
      array(32),
      array(1 | 8),
      array(8)
    );
  }
}
