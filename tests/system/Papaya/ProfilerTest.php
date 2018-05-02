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

require_once __DIR__.'/../../bootstrap.php';

class PapayaProfilerTest extends PapayaTestCase {

  /**
  * @covers PapayaProfiler::__construct
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $this->assertAttributeSame(
      $collector, '_collector', $profiler
    );
    $this->assertAttributeSame(
      $storage, '_storage', $profiler
    );
  }

  /**
  * @covers PapayaProfiler::setDivisor
  * @covers PapayaProfiler::allowRun
  */
  public function testDivisorWithZeroExpectingAllowRunFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $profiler->setDivisor(0);
    $this->assertFalse($profiler->allowRun());
  }

  /**
  * @covers PapayaProfiler::setDivisor
  * @covers PapayaProfiler::allowRun
  */
  public function testDivisorWithOneExpectingAllowRunTrue() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $profiler->setDivisor(1);
    $this->assertTrue($profiler->allowRun());
  }

  /**
  * @covers PapayaProfiler::setDivisor
  * @covers PapayaProfiler::allowRun
  */
  public function testDivisorWith50() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $profiler->setDivisor(50);
    $this->assertAttributeEquals(
      50, '_divisor', $profiler
    );
    $this->assertAttributeSame(
      NULL, '_allowRun', $profiler
    );
    $profiler->allowRun();
    $this->assertAttributeNotSame(
      NULL, '_allowRun', $profiler
    );
  }

  /**
  * @covers PapayaProfiler::setDivisor
  */
  public function testDivisorWithToLargeValueExpectingMaximum() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $profiler->setDivisor(50000000);
    $this->assertAttributeEquals(
      999999, '_divisor', $profiler
    );
    $this->assertAttributeSame(
      NULL, '_allowRun', $profiler
    );
  }

  /**
  * @covers PapayaProfiler::start
  */
  public function testStartWithAllowedRun() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $collector
      ->expects($this->once())
      ->method('enable');
    $profiler->setDivisor(1);
    $profiler->start();
  }

  /**
  * @covers PapayaProfiler::start
  */
  public function testStartWithoutAllowedRun() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
    $collector
      ->expects($this->never())
      ->method('enable');
    $profiler->setDivisor(0);
    $profiler->start();
  }

  /**
  * @covers PapayaProfiler::store
  */
  public function testStoreWithAllowedRun() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
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
  * @covers PapayaProfiler::store
  */
  public function testStoreWithoutAllowedRun() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerCollector $collector */
    $collector = $this->createMock(PapayaProfilerCollector::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaProfilerStorage $storage */
    $storage = $this->createMock(PapayaProfilerStorage::class);
    $profiler = new PapayaProfiler($collector, $storage);
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
