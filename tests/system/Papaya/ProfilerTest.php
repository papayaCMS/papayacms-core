<?php
require_once __DIR__.'/../../bootstrap.php';

class PapayaProfilerTest extends PapayaTestCase {

  /**
  * @covers PapayaProfiler::__construct
  */
  public function testConstructor() {
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
    $profiler->setDivisor(0);
    $this->assertFalse($profiler->allowRun());
  }

  /**
  * @covers PapayaProfiler::setDivisor
  * @covers PapayaProfiler::allowRun
  */
  public function testDivisorWithOneExpectingAllowRunTrue() {
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
    $profiler->setDivisor(1);
    $this->assertTrue($profiler->allowRun());
  }

  /**
  * @covers PapayaProfiler::setDivisor
  * @covers PapayaProfiler::allowRun
  */
  public function testDivisorWith50() {
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
    $profiler = new PapayaProfiler(
      $collector = $this->createMock(PapayaProfilerCollector::class),
      $storage = $this->createMock(PapayaProfilerStorage::class)
    );
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
