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

class PapayaMessageDispatcherCliTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::phpSapiName
  */
  public function testPhpSapiNameSet() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertSame(
      'nosapi',
      $dispatcher->phpSapiName()
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::phpSapiName
  */
  public function testPhpSapiNameInit() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->assertSame(
      PHP_SAPI,
      $dispatcher->phpSapiName()
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::allow
  */
  public function testAllowWithDisabledDispatcherExpectingFalse() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertFalse(
      $dispatcher->allow()
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::allow
  */
  public function testAllowExpectingTrue() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $dispatcher->phpSapiName('cli');
    $this->assertTrue(
      $dispatcher->allow()
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::getOptionsFromType
  */
  public function testGetOptionsFromType() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->assertContains(
      'Warning',
      $dispatcher->getOptionsFromType(\Papaya\Message::SEVERITY_WARNING)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::getOptionsFromType
  */
  public function testGetOptionsFromTypeWithInvalidTypeExpectingErrorOptions() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->assertContains(
      'Error',
      $dispatcher->getOptionsFromType(99999)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::dispatch
  */
  public function testDispatchWhileDisabledExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
    $message = $this->createMock(\PapayaMessageLogable::class);
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::dispatch
  */
  public function testDispatchWarning() {
    $context = $this->getMockBuilder(\Papaya\Message\Context\Interfaces\Text::class)->getMock();
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
    $message = $this->createMock(\PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_WARNING));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $dispatcher->phpSapiName('cli');
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(\Papaya\Message\Dispatcher\Cli::TARGET_STDERR, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nWarning: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::dispatch
  */
  public function testDispatchDebug() {
    $context = $this->createMock(\Papaya\Message\Context\Interfaces\Text::class);
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
    $message = $this->createMock(\PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_DEBUG));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $dispatcher->phpSapiName('cli');
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(\Papaya\Message\Dispatcher\Cli::TARGET_STDOUT, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nDebug: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::stream
  */
  public function testStreamGetAfterSet() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(\Papaya\Message\Dispatcher\Cli::TARGET_STDOUT, $output);
    $this->assertSame($output, $dispatcher->stream(\Papaya\Message\Dispatcher\Cli::TARGET_STDOUT));
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::stream
  */
  public function testStreamGetImplicitInitialization() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->assertInternalType(
      'resource' ,$dispatcher->stream(\Papaya\Message\Dispatcher\Cli::TARGET_STDERR)
    );
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::stream
  */
  public function testStreamGetWithInvalidTargetExpectingException() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid output target "fail".');
    $dispatcher->stream('fail', 0);
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Cli::stream
  */
  public function testStreamGetWithInvalidStreamExpectingException() {
    $dispatcher = new \Papaya\Message\Dispatcher\Cli();
    $this->expectException(UnexpectedValueException::class);
    $dispatcher->stream(\Papaya\Message\Dispatcher\Cli::TARGET_STDOUT, 0);
  }

}
