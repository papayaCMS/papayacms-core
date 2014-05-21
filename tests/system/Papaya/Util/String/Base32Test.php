<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUtilStringBase32Test extends PapayaTestCase {

  /**
  * @covers PapayaUtilStringBase32::encode
  * @dataProvider provideValidSamples
  */
  public function testEncode($unencoded, $encoded) {
    $this->assertEquals(
      $encoded,
      PapayaUtilStringBase32::encode($unencoded)
    );
  }

  /**
  * @covers PapayaUtilStringBase32::encode
  * @dataProvider provideValidSamplesWithPadding
  */
  public function testEncodeWithPadding($unencoded, $encoded) {
    $this->assertEquals(
      $encoded,
      PapayaUtilStringBase32::encode($unencoded, TRUE)
    );
  }

  /**
  * @covers PapayaUtilStringBase32::decode
  * @dataProvider provideValidSamples
  */
  public function testDecode($unencoded, $encoded) {
    $this->assertEquals(
      $unencoded,
      PapayaUtilStringBase32::decode($encoded)
    );
  }

  /**
  * @covers PapayaUtilStringBase32::decode
  * @dataProvider provideValidSamplesWithPadding
  */
  public function testDecodeWithPadding($unencoded, $encoded) {
    $this->assertEquals(
      $unencoded,
      PapayaUtilStringBase32::decode($encoded)
    );
  }

  /**
  * @covers PapayaUtilStringBase32::decode
  * @dataProvider provideInvalidDecodeSamples
  */
  public function testDecodeExpectingException($encoded) {
    $this->setExpectedException('OutOfBoundsException');
    PapayaUtilStringBase32::decode($encoded);
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function provideValidSamples() {
    return array(
      array('f' , 'my'),
      array('fo', 'mzxq'),
      array('foo', 'mzxw6'),
      array('foob', 'mzxw6yq'),
      array('fooba', 'mzxw6ytb'),
      array('foobar', 'mzxw6ytboi')
    );
  }

  public static function provideValidSamplesWithPadding() {
    return array(
      array('f' , 'my======'),
      array('fo', 'mzxq===='),
      array('foo', 'mzxw6==='),
      array('foob', 'mzxw6yq='),
      array('fooba', 'mzxw6ytb'),
      array('foobar', 'mzxw6ytboi======')
    );
  }

  public static function provideInvalidDecodeSamples() {
    return array(
      'length' => array('orsxg5'),
      'char' => array('0nqw24dmmuqhg5dsnfxgo'),
      'padding chars' => array('m3')
    );
  }
}