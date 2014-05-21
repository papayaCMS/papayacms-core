<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaMessageDisplayTranslatedTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDisplayTranslated::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessageDisplayTranslated(PapayaMessage::SEVERITY_INFO, 'Test');
    $string = $this->readAttribute($message, '_message');
    $this->assertInstanceOf(
      'PapayaUiStringTranslated', $string
    );
    $this->assertAttributeEquals(
      'Test', '_pattern', $string
    );
  }

  /**
  * @covers PapayaMessageDisplayTranslated::__construct
  */
  public function testConstructorWithArguments() {
    $message = new PapayaMessageDisplayTranslated(PapayaMessage::SEVERITY_INFO, 'Test', array(1, 2, 3));
    $string = $this->readAttribute($message, '_message');
    $this->assertAttributeEquals(
      array(1, 2, 3), '_values', $string
    );
  }
}