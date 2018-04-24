<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsFileNameTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFileName::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsFileName();
    $this->assertInstanceOf(PapayaFilterFileName::class, $profile->getFilter());
  }
}
