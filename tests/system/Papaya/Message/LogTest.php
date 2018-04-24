<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaMessageLogTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageLog::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertAttributeEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      '_group',
      $message
    );
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
  * @covers PapayaMessageLog::getGroup
  */
  public function testGetGroup() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      PapayaMessageLogable::GROUP_SYSTEM,
      $message->getGroup()
    );
  }


  /**
  * @covers PapayaMessageLog::getType
  */
  public function testGetType() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      PapayaMessage::SEVERITY_WARNING,
      $message->getType()
    );
  }

  /**
  * @covers PapayaMessageLog::SetContext
  */
  public function testSetContext() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $context = $this->createMock(PapayaMessageContextGroup::class);
    $message->setContext($context);
    $this->assertAttributeSame(
      $context,
      '_context',
      $message
    );
  }

  /**
  * @covers PapayaMessageLog::context
  */
  public function testContext() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $context = $this->createMock(PapayaMessageContextGroup::class);
    $message->setContext($context);
    $this->assertSame(
      $context,
      $message->context()
    );
  }

  /**
  * @covers PapayaMessageLog::getMessage
  */
  public function testGetMessage() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

  /**
  * @covers PapayaMessageLog::Context
  */
  public function testContextImplizitCreate() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      PapayaMessage::SEVERITY_WARNING,
      'Sample Message'
    );
    $this->assertInstanceOf(
      'PapayaMessageContextGroup',
      $message->context()
    );
  }
}
