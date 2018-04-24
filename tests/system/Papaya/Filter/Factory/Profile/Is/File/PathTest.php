<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsFilePathTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFilePath::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsFilePath();
    $this->assertInstanceOf(PapayaFilterFilePath::class, $profile->getFilter());
  }
}
