<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsEmailTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsEmail::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsEmail();
    $this->assertInstanceOf('PapayaFilterEmail', $profile->getFilter());
  }
}
