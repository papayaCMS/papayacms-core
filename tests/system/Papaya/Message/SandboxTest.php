<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaMessageSandboxTest extends PapayaTestCase {

  /**
   * @covers PapayaMessageSandbox::__construct
   */
  public function testConstructor() {
    $sandbox = new PapayaMessageSandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertAttributeEquals(
      array($this, 'callbackReturnImplodedArguments'), '_callback', $sandbox
    );
  }

  /**
   * @covers PapayaMessageSandbox::__invoke
   */
  public function testInvokeWithoutArguments() {
    $sandbox = new PapayaMessageSandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertEquals(
      '', $sandbox()
    );
  }

  /**
   * @covers PapayaMessageSandbox::__invoke
   */
  public function testInvokeWithSeveralArguments() {
    $sandbox = new PapayaMessageSandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertEquals(
      'one|two|three', $sandbox('one', 'two', 'three')
    );
  }

  /**
   * @covers PapayaMessageSandbox::__invoke
   */
  public function testInvokeWithErrorException() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessagePhpException'));
    $sandbox = new PapayaMessageSandbox(array($this, 'callbackThrowErrorException'));
    $sandbox->papaya($this->mockPapaya()->application(array('messages' => $messages)));
    $this->assertNull($sandbox());
  }

  /**
   * @covers PapayaMessageSandbox::__invoke
   */
  public function testInvokewithLogicException() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageException'));
    $sandbox = new PapayaMessageSandbox(array($this, 'callbackThrowException'));
    $sandbox->papaya($this->mockPapaya()->application(array('messages' => $messages)));
    $this->assertNull($sandbox());
  }

  /*********************
   * Callbacks
   ********************/

  public function callbackReturnImplodedArguments() {
    return implode('|', func_get_args());
  }

  public function callbackThrowErrorException() {
    throw new ErrorException('error');
  }

  public function callbackThrowException() {
    throw new LogicException('logic');
  }
}
