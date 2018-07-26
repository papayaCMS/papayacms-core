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

class PapayaProfilerTimerTest extends \PapayaTestCase {

  /**
   * @covers \PapayaProfilerTimer
   */
  public function testTimerWithOnTake() {
    $timer = new \PapayaProfilerTimer();
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
   * @covers \PapayaProfilerTimer
   */
  public function testTimerWithTwoTakes() {
    $timer = new \PapayaProfilerTimer();
    $timer->take('TEST');
    $timer->take('TEST');
    $takes = iterator_to_array($timer);
    $this->assertCount(2, $takes);
  }

  /**
   * @covers \PapayaProfilerTimer
   */
  public function testTimerWithTextWithArgument() {
    $timer = new \PapayaProfilerTimer();
    $timer->take('Hello %s!', 'World');
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertEquals('Hello World!', $takes[0]['text']);
  }

  /**
   * @covers \PapayaProfilerTimer
   */
  public function testTimerWithTextWithArrayArgument() {
    $timer = new \PapayaProfilerTimer();
    $timer->take('Hello %s and %s!', array('World', 'Universe'));
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertEquals('Hello World and Universe!', $takes[0]['text']);
  }

  /**
   * @covers \PapayaProfilerTimer
   */
  public function testEmit() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('log')
      ->with(
        \PapayaMessageLogable::GROUP_DEBUG,
        \PapayaMessage::SEVERITY_DEBUG,
        $this->isType('string'),
        $this->isInstanceOf(\PapayaMessageContextRuntime::class)
      );
    $timer = new \PapayaProfilerTimer();
    $timer->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $timer->take('TEST');
    $timer->emit();
  }
}
