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

namespace Papaya;
require_once __DIR__.'/../../bootstrap.php';

class ProfilerTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Profiler::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $this->assertSame(
      $collector, $profiler->getCollector()
    );
    $this->assertSame(
      $storage, $profiler->getStorage()
    );
  }

  /**
   * @covers \Papaya\Profiler::setDivisor
   * @covers \Papaya\Profiler::allowRun
   */
  public function testDivisorWithZeroExpectingAllowRunFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $profiler->setDivisor(0);
    $this->assertFalse($profiler->allowRun());
  }

  /**
   * @covers \Papaya\Profiler::setDivisor
   * @covers \Papaya\Profiler::allowRun
   */
  public function testDivisorWithOneExpectingAllowRunTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $profiler->setDivisor(1);
    $this->assertTrue($profiler->allowRun());
  }

  /**
   * @covers \Papaya\Profiler::setDivisor
   * @covers \Papaya\Profiler::allowRun
   */
  public function testDivisorWith50() {
    /** @var \PHPUnit\Framework\MockObject\MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $profiler->setDivisor(50);
    $this->assertEquals(
      50, $profiler->getDivisor()
    );
  }

  /**
   * @covers \Papaya\Profiler::setDivisor
   */
  public function testDivisorWithToLargeValueExpectingMaximum() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $profiler->setDivisor(50000000);
    $this->assertEquals(
      999999,  $profiler->getDivisor()
    );
  }

  /**
   * @covers \Papaya\Profiler::start
   */
  public function testStartWithAllowedRun() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $collector
      ->expects($this->once())
      ->method('enable');
    $profiler->setDivisor(1);
    $profiler->start();
  }

  /**
   * @covers \Papaya\Profiler::start
   */
  public function testStartWithoutAllowedRun() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $collector
      ->expects($this->never())
      ->method('enable');
    $profiler->setDivisor(0);
    $profiler->start();
  }

  /**
   * @covers \Papaya\Profiler::store
   */
  public function testStoreWithAllowedRun() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $collector
      ->expects($this->once())
      ->method('disable')
      ->will($this->returnValue(array('data')));
    $storage
      ->expects($this->once())
      ->method('saveRun')
      ->with(array('data'), 'papaya');
    $profiler->setDivisor(1);
    $profiler->store();
  }

  /**
   * @covers \Papaya\Profiler::store
   */
  public function testStoreWithoutAllowedRun() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Collector $collector */
    $collector = $this->createMock(Profiler\Collector::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|Profiler\Storage $storage */
    $storage = $this->createMock(Profiler\Storage::class);
    $profiler = new Profiler($collector, $storage);
    $collector
      ->expects($this->never())
      ->method('disable');
    $storage
      ->expects($this->never())
      ->method('saveRun');
    $profiler->setDivisor(0);
    $profiler->store();
  }
}
