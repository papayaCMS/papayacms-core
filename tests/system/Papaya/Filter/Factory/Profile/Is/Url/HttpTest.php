<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsUrlHttpTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrlHttp::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsUrlHttp();
    $this->assertInstanceOf('PapayaFilterUrlHttp', $profile->getFilter());
  }
}
