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

namespace Papaya\Profiler;
require_once __DIR__.'/../../../bootstrap.php';

class TimerTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Profiler\Timer
   */
  public function testTimerWithOnTake() {
    $timer = new Timer();
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
   * @covers \Papaya\Profiler\Timer
   */
  public function testTimerWithTwoTakes() {
    $timer = new Timer();
    $timer->take('TEST');
    $timer->take('TEST');
    $takes = iterator_to_array($timer);
    $this->assertCount(2, $takes);
  }

  /**
   * @covers \Papaya\Profiler\Timer
   */
  public function testTimerWithTextWithArgument() {
    $timer = new Timer();
    $timer->take('Hello %s!', 'World');
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertEquals('Hello World!', $takes[0]['text']);
  }

  /**
   * @covers \Papaya\Profiler\Timer
   */
  public function testTimerWithTextWithArrayArgument() {
    $timer = new Timer();
    $timer->take('Hello %s and %s!', array('World', 'Universe'));
    $takes = iterator_to_array($timer);
    $this->assertCount(1, $takes);
    $this->assertEquals('Hello World and Universe!', $takes[0]['text']);
  }

  /**
   * @covers \Papaya\Profiler\Timer
   */
  public function testEmit() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('log')
      ->with(
        \Papaya\Message\Logable::GROUP_DEBUG,
        \Papaya\Message::SEVERITY_DEBUG,
        $this->isType('string'),
        $this->isInstanceOf(\Papaya\Message\Context\Runtime::class)
      );
    $timer = new Timer();
    $timer->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $timer->take('TEST');
    $timer->emit();
  }
}
