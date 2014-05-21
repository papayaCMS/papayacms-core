<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsFileNameTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFileName::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsFileName();
    $this->assertInstanceOf('PapayaFilterFileName', $profile->getFilter());
  }
}