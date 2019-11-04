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

namespace Papaya\Message\Dispatcher {

  use Papaya\Message;
  use Papaya\TestCase;
  use Psr\Log\LoggerInterface;

  /**
   * @covers \Papaya\Message\Dispatcher\PSR3
   */
  class PSR3Test extends TestCase {

    /**
     * @param string $expected
     * @param int $severity
     * @testWith
     *   ["error", 2]
     *   ["debug", 3]
     */
    public function testSeverityIsMappedToLogLevel($expected, $severity) {
      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->with($expected, 'log message', []);

      $dispatcher = new PSR3($logger);
      $dispatcher->dispatch($this->createMessageFixture($severity));
    }

    public function testLoggerExceptionDisablesWrapper() {
      $messages = $this->createMock(Message\Manager::class);
      $messages
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->isInstanceOf(Message\Exception::class));

      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->willThrowException(new \Exception());

      $dispatcher = new PSR3($logger);
      $dispatcher->papaya($this->mockPapaya()->application(['messages' => $messages]));
      $this->assertTrue($dispatcher->isEnabled());
      $dispatcher->dispatch($this->createMessageFixture(Message::SEVERITY_INFO));
      $this->assertFalse($dispatcher->isEnabled());
    }

    public function testLogWithExceptionContext() {
      $context = $this->createMock(Message\Context\Exception::class);
      $context
        ->method('getException')
        ->willReturn($exception = new \Exception());

      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->with('error', 'message', ['exception' => $exception]);

      $dispatcher = new PSR3($logger);
      $dispatcher->dispatch($this->createMessageFixture(
        Message::SEVERITY_ERROR, 'message', [$context])
      );
    }

    public function testLogWithVariableContext() {
      $context = $this->createMock(Message\Context\Variable::class);
      $context
        ->method('asString')
        ->willReturn('context value');

      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->with('error', 'message', ['context value']);

      $dispatcher = new PSR3($logger);
      $dispatcher->dispatch($this->createMessageFixture(
        Message::SEVERITY_ERROR, 'message', [$context])
      );
    }

    public function testLogWithItemsContext() {
      $context = $this->createMock(Message\Context\Items::class);
      $context
        ->method('getLabel')
        ->willReturn('some items');
      $context
        ->method('asArray')
        ->willReturn([21, 42]);

      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->with('error', 'message', ['some items' => [21, 42]]);

      $dispatcher = new PSR3($logger);
      $dispatcher->dispatch($this->createMessageFixture(
        Message::SEVERITY_ERROR, 'message', [$context])
      );
    }

    public function testLogWithNestedGroupContext() {
      $context = $this->createMock(Message\Context\Items::class);
      $context
        ->method('getLabel')
        ->willReturn('some items');
      $context
        ->method('asArray')
        ->willReturn([21, 42]);
      $contextGroup = $this->createMock(Message\Context\Group::class);
      $contextGroup
        ->method('getIterator')
        ->willReturn(new \ArrayIterator([$context]));

      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->with('error', 'message', [['some items' => [21, 42]]]);

      $dispatcher = new PSR3($logger);
      $dispatcher->dispatch($this->createMessageFixture(
        Message::SEVERITY_ERROR, 'message', [$contextGroup])
      );
    }

    public function testLogWithDuplicateLabelsContext() {
      $one = $this->createMock(Message\Context\Items::class);
      $one
        ->method('getLabel')
        ->willReturn('some items');
      $one
        ->method('asArray')
        ->willReturn(['one']);
      $two = $this->createMock(Message\Context\Items::class);
      $two
        ->method('getLabel')
        ->willReturn('some items');
      $two
        ->method('asArray')
        ->willReturn(['two']);
      $three = $this->createMock(Message\Context\Items::class);
      $three
        ->method('getLabel')
        ->willReturn('some items');
      $three
        ->method('asArray')
        ->willReturn(['three']);

      $logger = $this->createMock(LoggerInterface::class);
      $logger
        ->expects($this->once())
        ->method('log')
        ->with('error', 'message', ['some items' => [['one'], ['two'], ['three']]]);

      $dispatcher = new PSR3($logger);
      $dispatcher->dispatch($this->createMessageFixture(
        Message::SEVERITY_ERROR, 'message', [$one, $two, $three])
      );
    }

    /**
     * @param string $severity
     * @param string $logMessage
     * @param array $contextValues
     * @return \PHPUnit_Framework_MockObject_MockObject|Message\Logable
     */
    private function createMessageFixture(
      $severity, $logMessage = 'log message', $contextValues = []
    ) {
      $context = new Message\Context\Group();
      foreach ($contextValues as $value) {
        $context->append($value);
      }
      $message = $this->createMock(Message\Logable::class);
      $message
        ->method('getSeverity')
        ->willReturn($severity);
      $message
        ->method('getMessage')
        ->willReturn($logMessage);
      $message
        ->method('context')
        ->willReturn($context);
      return $message;
    }
  }

}
