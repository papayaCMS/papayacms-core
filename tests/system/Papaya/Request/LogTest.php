<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaRequestLogTest extends PapayaTestCase {

  /**
  * @covers PapayaRequestLog::__construct
  */
  public function testConstructor() {
    $log = new PapayaRequestLog();
    $this->assertAttributeGreaterThan(
      0, '_startTime', $log
    );
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'Started at ', $events[0]
    );
  }

  /**
  * @covers PapayaRequestLog::getInstance
  */
  public function testGetInstanceExpectingSameInstance() {
    $this->assertSame(
      PapayaRequestLog::getInstance(TRUE),
      PapayaRequestLog::getInstance()
    );
  }

  /**
  * @covers PapayaRequestLog::getInstance
  */
  public function testGetInstanceExpectingDifferentInstances() {
    $this->assertNotSame(
      PapayaRequestLog::getInstance(TRUE),
      PapayaRequestLog::getInstance(TRUE)
    );
  }

  /**
  * @covers PapayaRequestLog::logTime
  */
  public function testLogTime() {
    $log = new PapayaRequestLog();
    $log->logTime('SAMPLE');
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'SAMPLE', $events[1]
    );
  }

  /**
  * @covers PapayaRequestLog::logTime
  */
  public function testLogTimeTwoMessages() {
    $log = new PapayaRequestLog();
    $log->logTime('SAMPLE');
    $log->logTime('SAMPLE');
    $events = $this->readAttribute($log, '_events');
    $this->assertStringStartsWith(
      'SAMPLE', $events[2]
    );
  }

  /**
  * @covers PapayaRequestLog::emit
  */
  public function testEmitWithStopMessage() {
    $messages = $this->getMock(PapayaMessageManager::class, array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageLog::class))
      ->will($this->returnCallback(array($this, 'checkLogMessageContextWithStop')));
    $log = new PapayaRequestLog();
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
  * @covers PapayaRequestLog::emit
  */
  public function testEmitWithoutStopMessage() {
    $messages = $this->getMock(PapayaMessageManager::class, array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageLog::class))
      ->will($this->returnCallback(array($this, 'checkLogMessageContext')));
    $log = new PapayaRequestLog();
    $log->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $log->emit(FALSE);
  }

  public function checkLogMessageContextWithStop($logMessage) {
    $this->assertCount(3, $logMessage->context());
  }

  public function checkLogMessageContext($logMessage) {
    $this->assertCount(2, $logMessage->context());
  }
}
