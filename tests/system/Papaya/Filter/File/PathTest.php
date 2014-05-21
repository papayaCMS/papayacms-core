<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterFilePathTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFilePath
   */
  public function testFilter() {
    $filter = new PapayaFilterFilePath();
    $this->assertTrue($filter->validate('/foo/bar/'));
  }

}