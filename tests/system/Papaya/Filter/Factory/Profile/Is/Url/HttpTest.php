<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsUrlHttpTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrlHttp::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsUrlHttp();
    $this->assertInstanceOf(PapayaFilterUrlHttp::class, $profile->getFilter());
  }
}
