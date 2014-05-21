<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsUrlHostTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrlHost::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsUrlHost();
    $this->assertInstanceOf('PapayaFilterUrlHost', $profile->getFilter());
  }
}
