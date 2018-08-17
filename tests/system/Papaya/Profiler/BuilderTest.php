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

class PapayaProfilerBuilderTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Profiler\Builder::createCollector
   */
  public function testCreateCollector() {
    $builder = new Builder();
    $this->assertInstanceOf(Collector\Xhprof::class, $builder->createCollector());
  }

  /**
   * @covers \Papaya\Profiler\Builder::createStorage
   */
  public function testCreateStorageExpectFile() {
    $builder = new Builder();
    $builder->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array('PAPAYA_PROFILER_STORAGE_DIRECTORY' => $this->createTemporaryDirectory())
          )
        )
      )
    );
    $storage = $builder->createStorage();
    $this->removeTemporaryDirectory();
    $this->assertInstanceOf(Storage\File::class, $storage);
  }

  /**
   * @covers \Papaya\Profiler\Builder::createStorage
   */
  public function testCreateStorageExpectXhgui() {
    $builder = new Builder();
    $builder->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_PROFILER_STORAGE' => 'xhgui'
            )
          )
        )
      )
    );
    $storage = $builder->createStorage();
    $this->assertInstanceOf(Storage\Xhgui::class, $storage);
  }
}
