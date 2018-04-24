<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileProfilerTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileProfiler::createObject
  */
  public function testCreateObjectProfilerInactive() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_PROFILER_ACTIVE' => FALSE
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new PapayaApplicationProfileProfiler();
    $profile->builder($this->getBuilderFixture());
    $profiler = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaProfiler', $profiler
    );
    $this->assertFalse(
      $profiler->allowRun()
    );
  }

  /**
  * @covers PapayaApplicationProfileProfiler::createObject
  */
  public function testCreateObjectProfilerActive() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_PROFILER_ACTIVE' => TRUE,
        'PAPAYA_PROFILER_DIVISOR' => 1
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new PapayaApplicationProfileProfiler();
    $profile->builder($this->getBuilderFixture());
    $profiler = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaProfiler', $profiler
    );
    $this->assertTrue(
      $profiler->allowRun()
    );
  }

  /**
  * @covers PapayaApplicationProfileProfiler::builder
  */
  public function testBuilderGetAfterSet() {
    $builder = $this->getMock('PapayaProfilerBuilder');
    $profile = new PapayaApplicationProfileProfiler();
    $profile->builder($builder);
    $this->assertSame($builder, $profile->builder());
  }

  /**
  * @covers PapayaApplicationProfileProfiler::builder
  */
  public function testBuilderGetImplicitCreate() {
    $profile = new PapayaApplicationProfileProfiler();
    $this->assertInstanceOf('PapayaProfilerBuilder', $profile->builder());
  }

  private function getBuilderFixture() {
    $builder = $this->getMock('PapayaProfilerBuilder');
    $builder
      ->expects($this->once())
      ->method('papaya')
      ->with($this->isInstanceOf('PapayaApplication'));
    $builder
      ->expects($this->once())
      ->method('createCollector')
      ->will($this->returnValue($this->getMock('PapayaProfilerCollector')));
    $builder
      ->expects($this->once())
      ->method('createStorage')
      ->will($this->returnValue($this->getMock('PapayaProfilerStorage')));
    return $builder;
  }
}
