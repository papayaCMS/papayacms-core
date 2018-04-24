<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterFilePathTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFilePath
   */
  public function testFilter() {
    $filter = new PapayaFilterFilePath();
    $this->assertTrue($filter->validate('/foo/bar/'));
  }

}
