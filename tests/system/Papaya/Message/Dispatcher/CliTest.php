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

namespace Papaya\Message\Dispatcher;
require_once __DIR__.'/../../../../bootstrap.php';

class CliTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::phpSAPIName
   */
  public function testPhpSAPINameSet() {
    $dispatcher = new CLI();
    $dispatcher->phpSAPIName('nosapi');
    $this->assertSame(
      'nosapi',
      $dispatcher->phpSAPIName()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::phpSAPIName
   */
  public function testPhpSAPINameInit() {
    $dispatcher = new CLI();
    $this->assertSame(
      PHP_SAPI,
      $dispatcher->phpSAPIName()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::allow
   */
  public function testAllowWithDisabledDispatcherExpectingFalse() {
    $dispatcher = new CLI();
    $dispatcher->phpSAPIName('nosapi');
    $this->assertFalse(
      $dispatcher->allow()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::allow
   */
  public function testAllowExpectingTrue() {
    $dispatcher = new CLI();
    $dispatcher->phpSAPIName('cli');
    $this->assertTrue(
      $dispatcher->allow()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::getLabelFromType
   */
  public function testGetOptionsFromType() {
    $dispatcher = new CLI();
    $this->assertContains(
      'Warning',
      $dispatcher->getLabelFromType(\Papaya\Message::SEVERITY_WARNING)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::getLabelFromType
   */
  public function testGetOptionsFromTypeWithInvalidTypeExpectingErrorOptions() {
    $dispatcher = new CLI();
    $this->assertContains(
      'Error',
      $dispatcher->getLabelFromType(99999)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::dispatch
   */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    $dispatcher = new CLI();
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::dispatch
   */
  public function testDispatchWhileDisabledExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
    $message = $this->createMock(\Papaya\Message\Logable::class);
    $dispatcher = new CLI();
    $dispatcher->phpSAPIName('nosapi');
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::dispatch
   */
  public function testDispatchWarning() {
    $context = $this->getMockBuilder(\Papaya\Message\Context\Interfaces\Text::class)->getMock();
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
    $message = $this->createMock(\Papaya\Message\Logable::class);
    $message
      ->expects($this->any())
      ->method('getSeverity')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_WARNING));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new CLI();
    $dispatcher->phpSAPIName('cli');
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(CLI::TARGET_STDERR, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nWarning: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::dispatch
   */
  public function testDispatchDebug() {
    $context = $this->createMock(\Papaya\Message\Context\Interfaces\Text::class);
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
    $message = $this->createMock(\Papaya\Message\Logable::class);
    $message
      ->expects($this->any())
      ->method('getSeverity')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_DEBUG));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new CLI();
    $dispatcher->phpSAPIName('cli');
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(CLI::TARGET_STDOUT, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nDebug: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::stream
   */
  public function testStreamGetAfterSet() {
    $dispatcher = new CLI();
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(CLI::TARGET_STDOUT, $output);
    $this->assertSame($output, $dispatcher->stream(CLI::TARGET_STDOUT));
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::stream
   */
  public function testStreamGetImplicitInitialization() {
    $dispatcher = new CLI();
    $this->assertInternalType(
      'resource', $dispatcher->stream(CLI::TARGET_STDERR)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::stream
   */
  public function testStreamGetWithInvalidTargetExpectingException() {
    $dispatcher = new CLI();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid output target "fail".');
    $dispatcher->stream('fail', 0);
  }

  /**
   * @covers \Papaya\Message\Dispatcher\CLI::stream
   */
  public function testStreamGetWithInvalidStreamExpectingException() {
    $dispatcher = new CLI();
    $this->expectException(\UnexpectedValueException::class);
    $dispatcher->stream(CLI::TARGET_STDOUT, 0);
  }

}
