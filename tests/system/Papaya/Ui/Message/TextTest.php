<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiMessageTextTest extends PapayaTestCase {

  /**
  * @covers PapayaUiMessageText::__construct
  */
  public function testConstructor() {
    $message = new PapayaUiMessageText(PapayaUiMessage::SEVERITY_ERROR, 'sample', 'content');
    $this->assertEquals(
      'content', $message->content
    );
  }

  /**
  * @covers PapayaUiMessageText::appendTo
  */
  public function testAppendTo() {
    $message = new PapayaUiMessageText(PapayaUiMessage::SEVERITY_ERROR, 'sample', 'content', TRUE);
    $this->assertEquals(
      '<error event="sample" occured="yes">content</error>', $message->getXml()
    );
  }

  /**
  * @covers PapayaUiMessageText::appendTo
  */
  public function testAppendToWithSpecialChars() {
    $message = new PapayaUiMessageText(PapayaUiMessage::SEVERITY_ERROR, 'sample', '<b>foo', TRUE);
    $this->assertEquals(
      '<error event="sample" occured="yes">&lt;b&gt;foo</error>', $message->getXml()
    );
  }

  /**
  * @covers PapayaUiMessageText::getContent
  * @covers PapayaUiMessageText::setContent
  */
  public function testGetXmlAfterSetXml() {
    $message = new PapayaUiMessageText(PapayaUiMessage::SEVERITY_ERROR, 'sample', '');
    $message->content = 'content';
    $this->assertEquals(
      'content', $message->content
    );
  }

}
