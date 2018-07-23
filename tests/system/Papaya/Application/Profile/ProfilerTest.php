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

use Papaya\Application\Profile\Profiler;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaApplicationProfileProfilerTest extends PapayaTestCase {

  /**
  * @covers Profiler::createObject
  */
  public function testCreateObjectProfilerInactive() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_PROFILER_ACTIVE' => FALSE
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new Profiler();
    $profile->builder($this->getBuilderFixture());
    $profiler = $profile->createObject($application);
    $this->assertInstanceOf(
      PapayaProfiler::class, $profiler
    );
    $this->assertFalse(
      $profiler->allowRun()
    );
  }

  /**
  * @covers Profiler::createObject
  */
  public function testCreateObjectProfilerActive() {
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_PROFILER_ACTIVE' => TRUE,
        'PAPAYA_PROFILER_DIVISOR' => 1
      )
    );
    $application = $this->mockPapaya()->application(array('options' => $options));
    $profile = new Profiler();
    $profile->builder($this->getBuilderFixture());
    $profiler = $profile->createObject($application);
    $this->assertInstanceOf(
      PapayaProfiler::class, $profiler
    );
    $this->assertTrue(
      $profiler->allowRun()
    );
  }

  /**
  * @covers Profiler::builder
  */
  public function testBuilderGetAfterSet() {
    $builder = $this->createMock(PapayaProfilerBuilder::class);
    $profile = new Profiler();
    $profile->builder($builder);
    $this->assertSame($builder, $profile->builder());
  }

  /**
  * @covers Profiler::builder
  */
  public function testBuilderGetImplicitCreate() {
    $profile = new Profiler();
    $this->assertInstanceOf(PapayaProfilerBuilder::class, $profile->builder());
  }

  private function getBuilderFixture() {
    $builder = $this->createMock(PapayaProfilerBuilder::class);
    $builder
      ->expects($this->once())
      ->method('papaya')
      ->with($this->isInstanceOf(PapayaApplication::class));
    $builder
      ->expects($this->once())
      ->method('createCollector')
      ->will($this->returnValue($this->createMock(PapayaProfilerCollector::class)));
    $builder
      ->expects($this->once())
      ->method('createStorage')
      ->will($this->returnValue($this->createMock(PapayaProfilerStorage::class)));
    return $builder;
  }
}
