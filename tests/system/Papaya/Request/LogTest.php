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

class PapayaRequestLogTest extends \PapayaTestCase {

  /**
  * @covers \PapayaRequestLog::__construct
  */
  public function testConstructor() {
    $log = new \PapayaRequestLog();
    $this->assertAttributeGreaterThan(
      0, '_startTime', $log
    );
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'Started at ', $events[0]
    );
  }

  /**
  * @covers \PapayaRequestLog::getInstance
  */
  public function testGetInstanceExpectingSameInstance() {
    $this->assertSame(
      \PapayaRequestLog::getInstance(TRUE),
      \PapayaRequestLog::getInstance()
    );
  }

  /**
  * @covers \PapayaRequestLog::getInstance
  */
  public function testGetInstanceExpectingDifferentInstances() {
    $this->assertNotSame(
      \PapayaRequestLog::getInstance(TRUE),
      \PapayaRequestLog::getInstance(TRUE)
    );
  }

  /**
  * @covers \PapayaRequestLog::logTime
  */
  public function testLogTime() {
    $log = new \PapayaRequestLog();
    $log->logTime('SAMPLE');
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'SAMPLE', $events[1]
    );
  }

  /**
  * @covers \PapayaRequestLog::logTime
  */
  public function testLogTimeTwoMessages() {
    $log = new \PapayaRequestLog();
    $log->logTime('SAMPLE');
    $log->logTime('SAMPLE');
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'SAMPLE', $events[2]
    );
  }

  /**
  * @covers \PapayaRequestLog::emit
  */
  public function testEmitWithStopMessage() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageLog::class))
      ->will($this->returnCallback(array($this, 'checkLogMessageContextWithStop')));
    $log = new \PapayaRequestLog();
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
  * @covers \PapayaRequestLog::emit
  */
  public function testEmitWithoutStopMessage() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageLog::class))
      ->will($this->returnCallback(array($this, 'checkLogMessageContext')));
    $log = new \PapayaRequestLog();
    $log->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $log->emit(FALSE);
  }

  public function checkLogMessageContextWithStop(\PapayaMessageLogable $logMessage) {
    $this->assertCount(3, $logMessage->context());
  }

  public function checkLogMessageContext(\PapayaMessageLogable $logMessage) {
    $this->assertCount(2, $logMessage->context());
  }
}
