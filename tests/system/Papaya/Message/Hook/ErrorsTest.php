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

namespace Papaya\Message\Hook;

require_once __DIR__.'/../../../../bootstrap.php';

class ErrorsTest extends \Papaya\TestCase {

  private $_errorReporting = 0;

  public function setUp(): void {
    $this->_errorReporting = error_reporting(E_ALL & ~E_STRICT);
  }

  public function tearDown(): void {
    error_reporting($this->_errorReporting);
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager);
    $this->assertAttributeSame(
      $manager,
      '_messageManager',
      $hook
    );
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::activate
   */
  public function testActivate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager);
    $hook->activate();
    $this->assertIsCallable(
      set_error_handler(array($this, 'callbackDummyErrorHandler'))
    );
    restore_error_handler();
    restore_error_handler();
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::deactivate
   */
  public function testDeactivate() {
    $this->markTestSkipped('Needs reimplementation, can not test for anonymous function');
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager);
    $hook->activate();
    $hook->deactivate();
    $this->assertNull(
      set_error_handler(array($this, 'callbackDummyErrorHandler'))
    );
    restore_error_handler();
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::checkErrorDuplicates
   */
  public function testCheckErrorDuplicatesExpectingZero() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager);
    $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 23);
    $this->assertSame(
      0,
      $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42)
    );
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::checkErrorDuplicates
   */
  public function testCheckErrorDuplicatesExpectingOne() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager);
    $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42);
    $this->assertSame(
      1,
      $hook->checkErrorDuplicates(E_USER_ERROR, 'file.php', 42)
    );
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::handle
   * @covers \Papaya\Message\Hook\Errors::handleException
   */
  public function testHandleWithNotice() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\PHP\Error::class));
    $hook = new Errors($manager);
    $hook->handle(E_USER_NOTICE, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::handle
   * @covers \Papaya\Message\Hook\Errors::handleException
   */
  public function testHandleWithError() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager);
    $this->expectException(\ErrorException::class);
    $hook->handle(E_USER_ERROR, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::handle
   * @covers \Papaya\Message\Hook\Errors::handleException
   */
  public function testHandleWithErrorPushedToExceptionHook() {
    $exceptionHook = $this
      ->getMockBuilder(Exceptions::class)
      ->disableOriginalConstructor()
      ->getMock();
    $exceptionHook
      ->expects($this->once())
      ->method('handle')
      ->with($this->isInstanceOf(\ErrorException::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Errors($manager, $exceptionHook);
    $hook->handle(E_USER_ERROR, 'Sample Message', 'file.php', 42, 'CONTEXT');
  }

  /**
   * @covers \Papaya\Message\Hook\Errors::handle
   * @covers \Papaya\Message\Hook\Errors::handleException
   */
  public function testHandleWithNoticeExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\PHP\Error::class))
      ->will(
        $this->returnCallback(
          array($this, 'callbackThrowANotice')
        )
      );
    $hook = new Errors($manager);
    $this->assertFalse(
      $hook->handle(E_USER_NOTICE, 'Sample Message', 'file.php', 42, 'CONTEXT')
    );
  }

  /***********************
   * Callbacks
   ***********************/

  public function callbackThrowANotice() {
    throw new \LogicException('Test');
  }

  public function callbackDummyErrorHandler() {
    return FALSE;
  }
}
