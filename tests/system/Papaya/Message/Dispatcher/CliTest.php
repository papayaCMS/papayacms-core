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

class PapayaMessageDispatcherCliTest extends PapayaTestCase {

  /**
  * @covers \PapayaMessageDispatcherCli::phpSapiName
  */
  public function testPhpSapiNameSet() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertSame(
      'nosapi',
      $dispatcher->phpSapiName()
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::phpSapiName
  */
  public function testPhpSapiNameInit() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->assertSame(
      PHP_SAPI,
      $dispatcher->phpSapiName()
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::allow
  */
  public function testAllowWithDisabledDispatcherExpectingFalse() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertFalse(
      $dispatcher->allow()
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::allow
  */
  public function testAllowExpectingTrue() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('cli');
    $this->assertTrue(
      $dispatcher->allow()
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::getOptionsFromType
  */
  public function testGetOptionsFromType() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->assertContains(
      'Warning',
      $dispatcher->getOptionsFromType(PapayaMessage::SEVERITY_WARNING)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::getOptionsFromType
  */
  public function testGetOptionsFromTypeWithInvalidTypeExpectingErrorOptions() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->assertContains(
      'Error',
      $dispatcher->getOptionsFromType(99999)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessage $message */
    $message = $this->createMock(PapayaMessage::class);
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchWhileDisabledExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $dispatcher = new \PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('nosapi');
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchWarning() {
    $context = $this->getMockBuilder(PapayaMessageContextInterfaceString::class)->getMock();
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
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
    $dispatcher = new \PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('cli');
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDERR, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nWarning: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::dispatch
  */
  public function testDispatchDebug() {
    $context = $this->createMock(PapayaMessageContextInterfaceString::class);
    $context
      ->expects($this->any())
      ->method('asString')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
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
    $dispatcher = new \PapayaMessageDispatcherCli();
    $dispatcher->phpSapiName('cli');
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT, $output);
    $dispatcher->dispatch($message);
    fseek($output, 0);
    $this->assertEquals(
      "\n\nDebug: Test Message CONTEXT\n",
      fread($output, 1024)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetAfterSet() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $output = fopen('php://memory', 'rwb');
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT, $output);
    $this->assertSame($output, $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT));
  }

  /**
  * @covers \PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetImplicitInitialization() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->assertInternalType(
      'resource' ,$dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDERR)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetWithInvalidTargetExpectingException() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid output target "fail".');
    $dispatcher->stream('fail', 0);
  }

  /**
  * @covers \PapayaMessageDispatcherCli::stream
  */
  public function testStreamGetWithInvalidStreamExpectingException() {
    $dispatcher = new \PapayaMessageDispatcherCli();
    $this->expectException(UnexpectedValueException::class);
    $dispatcher->stream(PapayaMessageDispatcherCli::TARGET_STDOUT, 0);
  }

}
