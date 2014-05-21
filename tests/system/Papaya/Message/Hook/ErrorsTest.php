<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaMessageHookErrorsTest extends PapayaTestCase {

  private $_errorReporting = 0;

  public function setUp() {
    $this->_errorReporting = error_reporting(E_ALL & ~E_STRICT);
  }

  public function tearDown() {
    error_reporting($this->_errorReporting);
  }

  /**
  * @covers PapayaMessageHookErrors::__construct
  */
  public function testConstructor() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager);
    $this->assertAttributeSame(
      $manager,
      '_messageManager',
      $hook
    );
  }

  /**
  * @covers PapayaMessageHookErrors::activate
  */
  public function testActivate() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager);
    $hook->activate();
    $this->assertSame(
      array($hook, 'handle'),
      set_error_handler(array($this, 'callbackDummyErrorHandler'))
    );
    restore_error_handler();
    restore_error_handler();
  }

  /**
  * @covers PapayaMessageHookErrors::deactivate
  */
  public function testDeactivate() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager);
    $hook->activate();
    $hook->deactivate();
    $this->assertNotSame(
      array($hook, 'handle'),
      set_error_handler(array($this, 'callbackDummyErrorHandler'))
    );
    restore_error_handler();
  }

  /**
  * @covers PapayaMessageHookErrors::checkErrorDuplicates
  */
  public function testCheckErrorDuplicatesExpectingZero() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager);
    $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 23);
    $this->assertSame(
      0,
      $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42)
    );
  }

  /**
  * @covers PapayaMessageHookErrors::checkErrorDuplicates
  */
  public function testCheckErrorDuplicatesExpectingOne() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager);
    $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42);
    $this->assertSame(
      1,
      $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42)
    );
  }

  /**
  * @covers PapayaMessageHookErrors::handle
  * @covers PapayaMessageHookErrors::handleException
  */
  public function testHandleWithNotice() {
    $manager = $this->getMock('PapayaMessageManager');
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessagePhpError'));
    $hook = new PapayaMessageHookErrors($manager);
    $hook->handle(E_USER_NOTICE, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
  * @covers PapayaMessageHookErrors::handle
  * @covers PapayaMessageHookErrors::handleException
  */
  public function testHandleWithError() {
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager);
    $this->setExpectedException('ErrorException');
    $hook->handle(E_USER_ERROR, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
  * @covers PapayaMessageHookErrors::handle
  * @covers PapayaMessageHookErrors::handleException
  */
  public function testHandleWithErrorPushedToExceptionHook() {
    $exceptionHook = $this
      ->getMockBuilder('PapayaMessageHookExceptions')
      ->disableOriginalConstructor()
      ->getMock();
    $exceptionHook
      ->expects($this->once())
      ->method('handle')
      ->with($this->isInstanceOf('ErrorException'));
    $manager = $this->getMock('PapayaMessageManager');
    $hook = new PapayaMessageHookErrors($manager, $exceptionHook);
    $hook->handle(E_USER_ERROR, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
  * @covers PapayaMessageHookErrors::handle
  * @covers PapayaMessageHookErrors::handleException
  */
  public function testHandleWithNoticeExpectingFalse() {
    $manager = $this->getMock('PapayaMessageManager');
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessagePhpError'))
      ->will(
        $this->returnCallback(
          array($this, 'callbackThrowANotice')
        )
      );
    $hook = new PapayaMessageHookErrors($manager);
    $this->assertFalse(
      $hook->handle(E_USER_NOTICE, 'Sample Message', 'file.php', 42, 'CONTEXT')
    );
  }

  /***********************
  * Callbacks
  ***********************/

  public function callbackThrowANotice() {
    throw new Exception();
  }

  public function callbackDummyErrorHandler() {
    return FALSE;
  }
}