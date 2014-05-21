<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiStringTest extends PapayaTestCase {

  /**
  * @covers PapayaUiString::__construct
  */
  public function testConstructor() {
    $string = new PapayaUiString('Hello %s!', array('World'));
    $this->assertAttributeEquals(
      'Hello %s!', '_pattern', $string
    );
    $this->assertAttributeEquals(
      array('World'), '_values', $string
    );
  }

  /**
  * @covers PapayaUiString::__construct
  */
  public function testConstructorWithPatternOnly() {
    $string = new PapayaUiString('Hello World!');
    $this->assertAttributeEquals(
      'Hello World!', '_pattern', $string
    );
    $this->assertAttributeEquals(
      array(), '_values', $string
    );
  }

  /**
  * @covers PapayaUiString::__toString
  * @covers PapayaUiString::compile
  * @dataProvider provideExamplesForToString
  */
  public function testMagicMethodToString($expected, $pattern, $values) {
    $string = new PapayaUiString($pattern, $values);
    $this->assertEquals(
      $expected, (string)$string
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideExamplesForToString() {
    return array(
      'string only' => array('Hello World!', 'Hello World!', array()),
      'single value' => array('Hello World!', 'Hello %s!', array('World')),
      'two values' => array('Hello 2. World!', 'Hello %d. %s!', array(2, 'World'))
    );
  }
}