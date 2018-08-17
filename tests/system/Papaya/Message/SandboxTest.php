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

namespace Papaya\Message;

require_once __DIR__.'/../../../bootstrap.php';

class SandboxTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Sandbox::__construct
   */
  public function testConstructor() {
    $sandbox = new Sandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertAttributeEquals(
      array($this, 'callbackReturnImplodedArguments'), '_callback', $sandbox
    );
  }

  /**
   * @covers \Papaya\Message\Sandbox::__invoke
   */
  public function testInvokeWithoutArguments() {
    $sandbox = new Sandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertEquals(
      '', $sandbox()
    );
  }

  /**
   * @covers \Papaya\Message\Sandbox::__invoke
   */
  public function testInvokeWithSeveralArguments() {
    $sandbox = new Sandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertEquals(
      'one|two|three', $sandbox('one', 'two', 'three')
    );
  }

  /**
   * @covers \Papaya\Message\Sandbox::__invoke
   */
  public function testInvokeWithErrorException() {
    $messages = $this->createMock(Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PHP\Exception::class));
    $sandbox = new Sandbox(array($this, 'callbackThrowErrorException'));
    $sandbox->papaya($this->mockPapaya()->application(array('messages' => $messages)));
    $this->assertNull($sandbox());
  }

  /**
   * @covers \Papaya\Message\Sandbox::__invoke
   */
  public function testInvokewithLogicException() {
    $messages = $this->createMock(Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(Exception::class));
    $sandbox = new Sandbox(array($this, 'callbackThrowException'));
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
    throw new \ErrorException('error');
  }

  public function callbackThrowException() {
    throw new \LogicException('logic');
  }
}
