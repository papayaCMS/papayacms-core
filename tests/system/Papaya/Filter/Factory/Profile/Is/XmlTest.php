<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsXml::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsXml();
    $this->assertInstanceOf(PapayaFilterXml::class, $profile->getFilter());
  }
}
