<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiMessagesTest extends PapayaTestCase {


  /**
  * @covers PapayaUiMessages::__construct
  * @covers PapayaUiMessages::appendTo
  * @covers PapayaUiMessages::getXml
  */
  public function testAppendTo() {
    $message = $this
      ->getMockBuilder('PapayaUiMessage')
      ->disableOriginalConstructor()
      ->getMock();
    $message
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $messages = new PapayaUiMessages;
    $messages[] = $message;
    $this->assertEquals(
      '<messages/>',
      $messages->getXml()
    );
  }

  /**
  * @covers PapayaUiMessages::appendTo
  */
  public function testAppendToWithoutElements() {
    $parent = $this
      ->getMockBuilder('PapayaXmlElement')
      ->disableOriginalConstructor()
      ->getMock();
    $parent
      ->expects($this->never())
      ->method('appendTo');
    $messages = new PapayaUiMessages;
    $this->assertNull(
      $messages->appendTo($parent)
    );
  }

  /**
  * @covers PapayaUiMessages::getXml
  */
  public function testgetXmlWithoutElements() {
    $messages = new PapayaUiMessages;
    $this->assertEquals(
      '', $messages->getXml()
    );
  }
}
