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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaMessageSandboxTest extends \PapayaTestCase {

  /**
   * @covers \PapayaMessageSandbox::__construct
   */
  public function testConstructor() {
    $sandbox = new \PapayaMessageSandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertAttributeEquals(
      array($this, 'callbackReturnImplodedArguments'), '_callback', $sandbox
    );
  }

  /**
   * @covers \PapayaMessageSandbox::__invoke
   */
  public function testInvokeWithoutArguments() {
    $sandbox = new \PapayaMessageSandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertEquals(
      '', $sandbox()
    );
  }

  /**
   * @covers \PapayaMessageSandbox::__invoke
   */
  public function testInvokeWithSeveralArguments() {
    $sandbox = new \PapayaMessageSandbox(array($this, 'callbackReturnImplodedArguments'));
    $this->assertEquals(
      'one|two|three', $sandbox('one', 'two', 'three')
    );
  }

  /**
   * @covers \PapayaMessageSandbox::__invoke
   */
  public function testInvokeWithErrorException() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessagePhpException::class));
    $sandbox = new \PapayaMessageSandbox(array($this, 'callbackThrowErrorException'));
    $sandbox->papaya($this->mockPapaya()->application(array('messages' => $messages)));
    $this->assertNull($sandbox());
  }

  /**
   * @covers \PapayaMessageSandbox::__invoke
   */
  public function testInvokewithLogicException() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageException::class));
    $sandbox = new \PapayaMessageSandbox(array($this, 'callbackThrowException'));
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
