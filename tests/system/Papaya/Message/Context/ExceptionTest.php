<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Message\Context {

  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Message\Context\Exception
   */
  class ExceptionTest extends TestCase {

    public function testGetException() {
      $context = new Exception($e = $this->createMock(\LogicException::class));
      $this->assertSame($e , $context->getException());
    }

    public function testGetLabel() {
      $context = new Exception($this->createMock(\LogicException::class));
      $this->assertSame('Exception', $context->getLabel());
    }

    public function testGetBacktraceContext() {
      $e = $this->createMock(\Exception::class);
      $context = new Exception($e);
      $this->assertInstanceOf(Backtrace::class, $context->getBacktraceContext());
    }

    public function testAsArray() {
      $backtraceContext = $this->createMock(Backtrace::class);
      $backtraceContext
        ->expects($this->once())
        ->method('asArray')
        ->willReturn(['success']);
      $context = $this->createPartialMock(Exception::class, ['getBacktraceContext']);
      $context
        ->method('getBacktraceContext')
        ->willReturn($backtraceContext);
      $this->assertSame(['success'], $context->asArray());
    }

    public function testAsString() {
      $backtraceContext = $this->createMock(Backtrace::class);
      $backtraceContext
        ->expects($this->once())
        ->method('asString')
        ->willReturn('success');
      $context = $this->createPartialMock(Exception::class, ['getBacktraceContext']);
      $context
        ->method('getBacktraceContext')
        ->willReturn($backtraceContext);
      $this->assertSame('success', $context->asString());
    }

    public function testAsXhtml() {
      $backtraceContext = $this->createMock(Backtrace::class);
      $backtraceContext
        ->expects($this->once())
        ->method('asXhtml')
        ->willReturn('<success/>');
      $context = $this->createPartialMock(Exception::class, ['getBacktraceContext']);
      $context
        ->method('getBacktraceContext')
        ->willReturn($backtraceContext);
      $this->assertSame('<success/>', $context->asXhtml());
    }
  }

}
