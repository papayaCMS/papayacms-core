<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiMessageTest extends PapayaTestCase {

  /**
  * @covers PapayaUiMessage::__construct
  */
  public function testConstructor() {
    $message = new PapayaUiMessage_TestProxy(PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $this->assertEquals(
      PapayaUiMessage::SEVERITY_ERROR, $message->severity
    );
    $this->assertEquals(
      'sample', $message->event
    );
  }

  /**
  * @covers PapayaUiMessage::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $message = new PapayaUiMessage_TestProxy(PapayaUiMessage::SEVERITY_ERROR, 'sample', TRUE);
    $this->assertTrue($message->occured);
  }

  /**
  * @covers PapayaUiMessage::appendMessageElement
  * @covers PapayaUiMessage::getTagName
  * @dataProvider provideTestMessages
  */
  public function testAppendTo($expectedXml, $severity, $event, $occured = FALSE) {
    $message = new PapayaUiMessage_TestProxy($severity, $event, $occured);
    $this->assertEquals(
      $expectedXml,
      $message->getXml()
    );
  }

  /**
  * @covers PapayaUiMessage::setSeverity
  */
  public function testSeverityGetAfterSet() {
    $message = new PapayaUiMessage_TestProxy(PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $message->severity = PapayaUiMessage::SEVERITY_WARNING;
    $this->assertEquals(
      PapayaUiMessage::SEVERITY_WARNING, $message->severity
    );
  }

  /**
  * @covers PapayaUiMessage::setSeverity
  */
  public function testSeverityWithInvalidValueExpectingException() {
    $message = new PapayaUiMessage_TestProxy(PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $this->setExpectedException(
      'InvalidArgumentException', 'Invalid severity for message.'
    );
    $message->severity = 99;
  }

  /**
  * @covers PapayaUiMessage::setEvent
  */
  public function testEventGetAfterSet() {
    $message = new PapayaUiMessage_TestProxy(PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $message->event = 'success';
    $this->assertEquals(
      'success', $message->event
    );
  }

  /**
  * @covers PapayaUiMessage::setOccured
  */
  public function testOccuredGetAfterSet() {
    $message = new PapayaUiMessage_TestProxy(PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $message->occured = TRUE;
    $this->assertTrue(
      $message->occured
    );
  }

  /********************************
  * Data provider
  ********************************/

  public static function provideTestMessages() {
    return array(
      'sample error, not occured' => array(
        '<error event="sample" occured="no"/>',
        PapayaUiMessage::SEVERITY_ERROR,
        'sample',
        FALSE
      ),
      'test information, occured' => array(
        '<information event="test" occured="yes"/>',
        PapayaUiMessage::SEVERITY_INFORMATION,
        'test',
        TRUE
      ),
    );
  }
}

/**
 * @property mixed severity
 */
class PapayaUiMessage_TestProxy extends PapayaUiMessage {

  public function appendTo(PapayaXmlElement $parent) {
    return parent::appendMessageElement($parent);
  }
}