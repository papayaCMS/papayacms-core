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

class ExceptionsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Hook\Exceptions::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Exceptions($manager);
    $this->assertSame(
      $manager,
      $hook->getMessageManager()
    );
  }

  /**
   * @covers \Papaya\Message\Hook\Exceptions::activate
   */
  public function testActivate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Exceptions($manager);
    $hook->activate();
    $this->assertIsCallable(
      set_exception_handler(array($this, 'callbackDummyExceptionHandler'))
    );
    restore_exception_handler();
    restore_exception_handler();
  }

  /**
   * @covers \Papaya\Message\Hook\Exceptions::deactivate
   */
  public function testDeactivate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $hook = new Exceptions($manager);
    $hook->activate();
    $hook->deactivate();
    $this->assertIsNotCallable(
      set_exception_handler(array($this, 'callbackDummyExceptionHandler'))
    );
    restore_exception_handler();
  }

  /**
   * @covers \Papaya\Message\Hook\Exceptions::handle
   */
  public function testHandleWithErrorException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\PHP\Exception::class));
    $hook = new Exceptions($manager);
    $hook->handle(new \ErrorException('Sample Message', 0, E_USER_ERROR, 'file.php', 42));
  }

  /**
   * @covers \Papaya\Message\Hook\Exceptions::handle
   */
  public function testHandleWithException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Manager $manager */
    $manager = $this->createMock(\Papaya\Message\Manager::class);
    $manager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\PHP\Exception::class));
    $hook = new Exceptions($manager);
    $hook->handle(new \Exception('Sample Message'));
  }

  /***********************
   * Callbacks
   ***********************/

  public function callbackDummyExceptionHandler() {
    return FALSE;
  }
}
