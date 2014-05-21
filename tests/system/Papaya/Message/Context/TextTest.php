<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaMessageContextTextTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageContextText::__construct
  */
  public function testConstructor() {
    $context = new PapayaMessageContextText('Hello World');
    $this->assertAttributeSame(
      'Hello World',
      '_text',
      $context
    );
  }

  /**
  * @covers PapayaMessageContextText::asString
  */
  public function testAsString() {
    $context = new PapayaMessageContextText('Hello World');
    $this->assertEquals(
      'Hello World',
      $context->asString()
    );
  }
}