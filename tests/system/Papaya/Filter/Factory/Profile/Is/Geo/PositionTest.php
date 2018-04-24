<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsGeoPositionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGeoPosition::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsGeoPosition();
    $this->assertInstanceOf(PapayaFilterGeoPosition::class, $profile->getFilter());
  }
}
