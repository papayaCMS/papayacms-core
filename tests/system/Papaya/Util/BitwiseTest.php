<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUtilBitwiseTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilBitwise::inBitmask
  * @dataProvider provideInBitmaskPositiveData
  */
  public function testInBitmaskExpectingTrue($bit, $bitmask) {
    $this->assertTrue(
      PapayaUtilBitwise::inBitmask($bit, $bitmask)
    );
  }

  /**
  * @covers PapayaUtilBitwise::inBitmask
  * @dataProvider provideInBitmaskNegativeData
  */
  public function testInBitmaskExpectingFalse($bit, $bitmask) {
    $this->assertFalse(
      PapayaUtilBitwise::inBitmask($bit, $bitmask)
    );
  }

  /**
   * @covers PapayaUtilBitwise::union
   * @dataProvider provideUnionData
   */
  public function testUnion($expected, array $bits) {
    $this->assertEquals($expected, call_user_func_array('PapayaUtilBitwise::union', $bits));
  }

  /****************************************
  * Data Provider
  ****************************************/

  public static function provideInBitmaskPositiveData() {
    return array(
      array(0, 0),
      array(1, 3),
      array(2, 6),
      array(2, 7),
      array(1, 129)
    );
  }

  public static function provideInBitmaskNegativeData() {
    return array(
      array(1, 0),
      array(1, 6),
      array(2, 4),
      array(2, 128)
    );
  }

  public static function provideUnionData() {
    return array(
      array(1, array(1)),
      array(3, array(1, 2)),
      array(3, array(1, 2, 2)),
      array(6, array(2, 4, 0))
    );
  }
}