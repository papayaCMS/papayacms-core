<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsXml::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsXml();
    $this->assertInstanceOf('PapayaFilterXml', $profile->getFilter());
  }
}
