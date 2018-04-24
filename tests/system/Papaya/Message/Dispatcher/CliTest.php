<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageDispatcherCliTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageDispatcherCli::phpSapiName
  */
  public function testPhpSapiNameSet() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertSame(
      'nosapi',
      $dispatcher->phpSapiName()
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::phpSapiName
  */
  public function testPhpSapiNameInit() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->assertSame(
      php_sapi_name(),
      $dispatcher->phpSapiName()
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::allow
  */
  public function testAllowWithDisabledDispatcherExpectingFalse() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertFalse(
      $dispatcher->allow()
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::allow
  */
  public function testAllowExpectingTrue() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('cli');
    $this->assertTrue(
      $dispatcher->allow()
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::getOptionsFromType
  */
  public function testGetOptionsFromType() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->assertContains(
      'Warning',
      $dispatcher->getOptionsFromType(PapayaMessage::SEVERITY_WARNING)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::getOptionsFromType
  */
  public function testGetOptionsFromTypeWithInvalidTypeExpectingErrorOptions() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->assertContains(
      'Error',
      $dispatcher->getOptionsFromType(99999)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    $message = $this->createMock(PapayaMessage::class);
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchWhileDisabledExpectingFalse() {
    $message = $this->createMock(PapayaMessageLogable::class);
    $dispatcher = new PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchWarning() {
    $context = $this->getMockBuilder(PapayaMessageContextInterfaceString::class)->getMock();
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_WARNING));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('cli');
    $output = fopen('php://memory', 'rw');
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDERR, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nWarning: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchDebug() {
    $context = $this->getMockBuilder(PapayaMessageContextInterfaceString::class)->getMock();
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_DEBUG));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('cli');
    $output = fopen('php://memory', 'rw');
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nDebug: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetAfterSet() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $output = fopen('php://memory', 'rw');
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT, $output);
    $this->assertSame($output, $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT));
  }

  /**
  * @covers PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetImplicitInitialization() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->assertInternalType(
      'resource' ,$dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDERR)
    );
  }

  /**
  * @covers PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetWithInvalidTargetExpectingException() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->setExpectedException(InvalidArgumentException::class, 'Invalid output target "fail".');
    $dispatcher->stream('fail', 0);
  }

  /**
  * @covers PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetWithInvalidStreamExpectingException() {
    $dispatcher = new PapayaMessageDispatcherCli();
    $this->expectException(UnexpectedValueException::class);
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT, 0);
  }

}
