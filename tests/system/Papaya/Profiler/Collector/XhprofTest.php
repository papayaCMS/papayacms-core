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

namespace Papaya\Profiler\Collector;
require_once __DIR__.'/../../../../bootstrap.php';

class XhprofTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Profiler\Collector\Xhprof::enable
   */
  public function testEnable() {
    $this->skipIfNotExtensionLoaded('xhprof');
    $collector = new Xhprof();
    $this->assertTrue($collector->enable());
    $collector->disable();
  }

  /**
   * @covers \Papaya\Profiler\Collector\Xhprof::enable
   */
  public function testEnableNoXhProf() {
    $this->skipIfExtensionLoaded('xhprof');
    $collector = new Xhprof();
    $this->assertFalse($collector->enable());
  }

  /**
   * @covers \Papaya\Profiler\Collector\Xhprof::disable
   */
  public function testDisable() {
    $this->skipIfNotExtensionLoaded('xhprof');
    $collector = new Xhprof();
    $collector->enable();
    $this->assertInternalType(
      'array',
      $collector->disable()
    );
  }

  /**
   * @covers \Papaya\Profiler\Collector\Xhprof::disable
   */
  public function testDisableNoEnabled() {
    $collector = new Xhprof();
    $this->assertNull(
      $collector->disable()
    );
  }

  private function skipIfNotExtensionLoaded($extension) {
    if (!extension_loaded($extension)) {
      $this->markTestSkipped('Extension "'.$extension.'" not loaded.');
    }
  }

  private function skipIfExtensionLoaded($extension) {
    if (extension_loaded($extension)) {
      $this->markTestSkipped('Extension "'.$extension.'" loaded.');
    }
  }
}
