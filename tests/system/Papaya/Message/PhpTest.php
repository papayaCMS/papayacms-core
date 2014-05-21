<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaMessagePhpTest extends PapayaTestCase {

  /**
  * @covers PapayaMessagePhp::__construct
  */
  public function testConstructor() {
    $message = new PapayaMessagePhp();
    $this->assertAttributeInstanceOf(
      'PapayaMessageContextGroup',
      '_context',
      $message
    );
  }

  /**
  * @covers PapayaMessagePhp::setSeverity
  */
  public function testSetSeverity() {
    $message = new PapayaMessagePhp();
    $message->setSeverity(E_USER_NOTICE);
    $this->assertAttributeEquals(
      PapayaMessage::SEVERITY_INFO,
      '_type',
      $message
    );
  }

  /**
  * @covers PapayaMessagePhp::getGroup
  */
  public function testGetGroup() {
    $message = new PapayaMessagePhp();
    $this->assertEquals(
      PapayaMessageLogable::GROUP_PHP,
      $message->getGroup()
    );
  }

  /**
  * @covers PapayaMessagePhp::getType
  */
  public function testGetType() {
    $message = new PapayaMessagePhp();
    $this->assertEquals(
      PapayaMessage::SEVERITY_ERROR,
      $message->getType()
    );
  }

  /**
  * @covers PapayaMessagePhp::getMessage
  */
  public function testGetMessage() {
    $message = new PapayaMessagePhp();
    $this->assertSame(
      '',
      $message->getMessage()
    );
  }

  /**
  * @covers PapayaMessagePhp::context
  */
  public function testContext() {
    $message = new PapayaMessagePhp();
    $this->assertInstanceOf(
      'PapayaMessageContextGroup',
      $message->context()
    );
  }

  /**
  * @covers PapayaMessagePhp::setContext
  */
  public function testSetContext() {
    $context = $this->getMock('PapayaMessageContextGroup');
    $message = new PapayaMessagePhp();
    $message->setContext($context);
    $this->assertAttributeSame(
      $context,
      '_context',
      $message
    );
  }
}