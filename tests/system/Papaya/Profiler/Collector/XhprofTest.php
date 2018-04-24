<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaProfilerCollectorXhprofTest extends PapayaTestCase {

  /**
  * @covers PapayaProfilerCollectorXhprof::enable
  */
  public function testEnable() {
    $this->skipIfNotExtensionLoaded('xhprof');
    $collector = new PapayaProfilerCollectorXhprof();
    $this->assertTrue($collector->enable());
    $collector->disable();
  }

  /**
  * @covers PapayaProfilerCollectorXhprof::enable
  */
  public function testEnableNoXhProf() {
    $this->skipIfExtensionLoaded('xhprof');
    $collector = new PapayaProfilerCollectorXhprof();
    $this->assertFalse($collector->enable());
  }

  /**
  * @covers PapayaProfilerCollectorXhprof::disable
  */
  public function testDisable() {
    $this->skipIfNotExtensionLoaded('xhprof');
    $collector = new PapayaProfilerCollectorXhprof();
    $collector->enable();
    $this->assertInternalType(
      'array',
      $collector->disable()
    );
  }

  /**
  * @covers PapayaProfilerCollectorXhprof::disable
  */
  public function testDisableNoEnabled() {
    $collector = new PapayaProfilerCollectorXhprof();
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
