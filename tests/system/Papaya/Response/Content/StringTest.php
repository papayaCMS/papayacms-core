<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaResponseContentStringTest extends PapayaTestCase {

  /**
  * @covers PapayaResponseContentString::__construct
  */
  public function testConstructor() {
    $content = new PapayaResponseContentString('sample');
    $this->assertAttributeEquals(
      'sample', '_content', $content
    );
  }

  /**
  * @covers PapayaResponseContentString::length
  */
  public function testLength() {
    $content = new PapayaResponseContentString('sample');
    $this->assertEquals(6, $content->length());
  }

  /**
  * @covers PapayaResponseContentString::output
  */
  public function testOutput() {
    $content = new PapayaResponseContentString('sample');
    ob_start();
    $content->output();
    $this->assertEquals('sample', ob_get_clean());
  }

  /**
  * @covers PapayaResponseContentString::__toString
  */
  public function testMagicMethodToString() {
    $content = new PapayaResponseContentString('sample');
    $this->assertEquals('sample', (string)$content);
  }

}