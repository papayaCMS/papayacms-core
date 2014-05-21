<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaProfilerTimerTest extends PapayaTestCase {

  /**
   * @covers PapayaProfilerTimer
   */
  public function testTimerWithOnTake() {
    $timer = new PapayaProfilerTimer();
    usleep(50);
    $timer->take('TEST');
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertGreaterThan(0, $takes[0]['time']);
    $this->assertGreaterThan(0, $takes[0]['start']);
    $this->assertGreaterThan(0, $takes[0]['end']);
    $this->assertNotEmpty($takes[0]['time_string']);
    $this->assertEquals('TEST', $takes[0]['text']);
  }

  /**
   * @covers PapayaProfilerTimer
   */
  public function testTimerWithTwoTakes() {
    $timer = new PapayaProfilerTimer();
    $timer->take('TEST');
    $timer->take('TEST');
    $takes = iterator_to_array($timer);
    $this->assertCount(2, $takes);
  }

  /**
   * @covers PapayaProfilerTimer
   */
  public function testTimerWithTextWithArgument() {
    $timer = new PapayaProfilerTimer();
    $timer->take('Hello %s!', 'World');
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertEquals('Hello World!', $takes[0]['text']);
  }

  /**
   * @covers PapayaProfilerTimer
   */
  public function testTimerWithTextWithArrayArgument() {
    $timer = new PapayaProfilerTimer();
    $timer->take('Hello %s and %s!', array('World', 'Universe'));
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertEquals('Hello World and Universe!', $takes[0]['text']);
  }

  /**
   * @covers PapayaProfilerTimer
   */
  public function testEmit() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('log')
      ->with(
        PapayaMessageLogable::GROUP_DEBUG,
        PapayaMessage::SEVERITY_DEBUG,
        $this->isType('string'),
        $this->isInstanceOf('PapayaMessageContextRuntime')
      );
    $timer = new PapayaProfilerTimer();
    $timer->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $timer->take('TEST');
    $timer->emit();
  }
}