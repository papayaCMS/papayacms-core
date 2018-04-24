<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageHookExceptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaMessageHookExceptions::__construct
  */
  public function testConstructor() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookExceptions($manager);
    $this->assertAttributeSame(
      $manager,
      '_messageManager',
      $hook
    );
  }

  /**
  * @covers PapayaMessageHookExceptions::activate
  */
  public function testActivate() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookExceptions($manager);
    $hook->activate();
    $this->assertSame(
      array($hook, 'handle'),
      set_exception_handler(array($this, 'callbackDummyExceptionHandler'))
    );
    restore_exception_handler();
    restore_exception_handler();
  }

  /**
  * @covers PapayaMessageHookExceptions::deactivate
  */
  public function testDeactivate() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookExceptions($manager);
    $hook->activate();
    $hook->deactivate();
    $this->assertNotSame(
      array($hook, 'handle'),
      set_error_handler(array($this, 'callbackDummyExceptionHandler'))
    );
    restore_exception_handler();
  }

  /**
  * @covers PapayaMessageHookExceptions::handle
  */
  public function testHandleWithErrorException() {
    $manager = $this->getMock('PapayaMessageManager');
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessagePhpException'));
    $hook = new PapayaMessageHookExceptions($manager);
    $hook->handle(new ErrorException('Sample Message', 0, E_USER_ERROR, 'file.php', 42));
  }

  /**
  * @covers PapayaMessageHookExceptions::handle
  */
  public function testHandleWithException() {
    $manager = $this->getMock('PapayaMessageManager');
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessagePhpException'));
    $hook = new PapayaMessageHookExceptions($manager);
    $hook->handle(new Exception('Sample Message'));
  }

  /***********************
  * Callbacks
  ***********************/

  public function callbackDummyExceptionHandler() {
    return FALSE;
  }
}
