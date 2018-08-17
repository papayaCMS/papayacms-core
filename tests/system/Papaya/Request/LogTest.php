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

namespace Papaya\Request;
require_once __DIR__.'/../../../bootstrap.php';

class LogTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Request\Log::__construct
   */
  public function testConstructor() {
    $log = new Log();
    $this->assertAttributeGreaterThan(
      0, '_startTime', $log
    );
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'Started at ', $events[0]
    );
  }

  /**
   * @covers \Papaya\Request\Log::getInstance
   */
  public function testGetInstanceExpectingSameInstance() {
    $this->assertSame(
      Log::getInstance(TRUE),
      Log::getInstance()
    );
  }

  /**
   * @covers \Papaya\Request\Log::getInstance
   */
  public function testGetInstanceExpectingDifferentInstances() {
    $this->assertNotSame(
      Log::getInstance(TRUE),
      Log::getInstance(TRUE)
    );
  }

  /**
   * @covers \Papaya\Request\Log::logTime
   */
  public function testLogTime() {
    $log = new Log();
    $log->logTime('SAMPLE');
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'SAMPLE', $events[1]
    );
  }

  /**
   * @covers \Papaya\Request\Log::logTime
   */
  public function testLogTimeTwoMessages() {
    $log = new Log();
    $log->logTime('SAMPLE');
    $log->logTime('SAMPLE');
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'SAMPLE', $events[2]
    );
  }

  /**
   * @covers \Papaya\Request\Log::emit
   */
  public function testEmitWithStopMessage() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Log::class))
      ->will($this->returnCallback(array($this, 'checkLogMessageContextWithStop')));
    $log = new Log();
    $log->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $log->emit();
  }

  /**
   * @covers \Papaya\Request\Log::emit
   */
  public function testEmitWithoutStopMessage() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Log::class))
      ->will($this->returnCallback(array($this, 'checkLogMessageContext')));
    $log = new Log();
    $log->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $log->emit(FALSE);
  }

  public function checkLogMessageContextWithStop(\Papaya\Message\Logable $logMessage) {
    $this->assertCount(3, $logMessage->context());
  }

  public function checkLogMessageContext(\Papaya\Message\Logable $logMessage) {
    $this->assertCount(2, $logMessage->context());
  }
}
