<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaMessageDisplayTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDisplay::__construct
  * @covers PapayaMessageDisplay::_isValidType
  */
  public function testConstructor() {
    $message = new PapayaMessageDisplay(PapayaMessage::SEVERITY_WARNING, 'Sample Message');
    $this->assertAttributeEquals(
      PapayaMessage::SEVERITY_WARNING,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
  }

  /**
  * @covers PapayaMessageDisplay::__construct
  * @covers PapayaMessageDisplay::_isValidType
  */
  public function testConstructorWithInvalidTypeExpectingException() {
    $this->setExpectedException(InvalidArgumentException::class);
    new PapayaMessageDisplay(PapayaMessage::SEVERITY_DEBUG, 'Sample Message');
  }

  /**
  * @covers PapayaMessageDisplay::getType
  */
  public function testGetType() {
    $message = new PapayaMessageDisplay(PapayaMessage::SEVERITY_WARNING, 'Sample Message');
    $this->assertEquals(
      PapayaMessage::SEVERITY_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers PapayaMessageDisplay::getMessage
  */
  public function testGetMessage() {
    $message = new PapayaMessageDisplay(PapayaMessage::SEVERITY_WARNING, 'Sample Message');
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

}
