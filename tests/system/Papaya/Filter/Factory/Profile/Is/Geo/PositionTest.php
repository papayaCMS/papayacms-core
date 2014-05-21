<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsGeoPositionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGeoPosition::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsGeoPosition();
    $this->assertInstanceOf('PapayaFilterGeoPosition', $profile->getFilter());
  }
}
