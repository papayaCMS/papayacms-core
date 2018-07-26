<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageHookErrorsTest extends PapayaTestCase {

  private $_errorReporting = 0;

  public function setUp() {
    $this->_errorReporting = error_reporting(E_ALL & ~E_STRICT);
  }

  public function tearDown() {
    error_reporting($this->_errorReporting);
  }

  /**
  * @covers \PapayaMessageHookErrors::__construct
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager);
    $this->assertAttributeSame(
      $manager,
      '_messageManager',
      $hook
    );
  }

  /**
  * @covers \PapayaMessageHookErrors::activate
  */
  public function testActivate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager);
    $hook->activate();
    $this->assertSame(
      array($hook, 'handle'),
      set_error_handler(array($this, 'callbackDummyErrorHandler'))
    );
    restore_error_handler();
    restore_error_handler();
  }

  /**
  * @covers \PapayaMessageHookErrors::deactivate
  */
  public function testDeactivate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager);
    $hook->activate();
    $hook->deactivate();
    $this->assertNotSame(
      array($hook, 'handle'),
      set_error_handler(array($this, 'callbackDummyErrorHandler'))
    );
    restore_error_handler();
  }

  /**
  * @covers \PapayaMessageHookErrors::checkErrorDuplicates
  */
  public function testCheckErrorDuplicatesExpectingZero() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager);
    $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 23);
    $this->assertSame(
      0,
      $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42)
    );
  }

  /**
  * @covers \PapayaMessageHookErrors::checkErrorDuplicates
  */
  public function testCheckErrorDuplicatesExpectingOne() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager);
    $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42);
    $this->assertSame(
      1,
      $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42)
    );
  }

  /**
  * @covers \PapayaMessageHookErrors::handle
  * @covers \PapayaMessageHookErrors::handleException
  */
  public function testHandleWithNotice() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessagePhpError::class));
    $hook = new \PapayaMessageHookErrors($manager);
    $hook->handle(E_USER_NOTICE, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
  * @covers \PapayaMessageHookErrors::handle
  * @covers \PapayaMessageHookErrors::handleException
  */
  public function testHandleWithError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager);
    $this->expectException(ErrorException::class);
    $hook->handle(E_USER_ERROR, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
  * @covers \PapayaMessageHookErrors::handle
  * @covers \PapayaMessageHookErrors::handleException
  */
  public function testHandleWithErrorPushedToExceptionHook() {
    $exceptionHook = $this
      ->getMockBuilder(PapayaMessageHookExceptions::class)
      ->disableOriginalConstructor()
      ->getMock();
    $exceptionHook
      ->expects($this->once())
      ->method('handle')
      ->with($this->isInstanceOf(ErrorException::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $hook = new \PapayaMessageHookErrors($manager, $exceptionHook);
    $hook->handle(E_USER_ERROR, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
  * @covers \PapayaMessageHookErrors::handle
  * @covers \PapayaMessageHookErrors::handleException
  */
  public function testHandleWithNoticeExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageManager $manager */
    $manager = $this->createMock(PapayaMessageManager::class);
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessagePhpError::class))
      ->will(
        $this->returnCallback(
          array($this, 'callbackThrowANotice')
        )
      );
    $hook = new \PapayaMessageHookErrors($manager);
    $this->assertFalse(
      $hook->handle(E_USER_NOTICE, 'Sample Message', 'file.php', 42, 'CONTEXT')
    );
  }

  /***********************
  * Callbacks
  ***********************/

  public function callbackThrowANotice() {
    throw new LogicException('Test');
  }

  public function callbackDummyErrorHandler() {
    return FALSE;
  }
}
