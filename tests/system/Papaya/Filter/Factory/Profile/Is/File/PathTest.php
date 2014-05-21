<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsFilePathTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFilePath::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsFilePath();
    $this->assertInstanceOf('PapayaFilterFilePath', $profile->getFilter());
  }
}