<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsFloatTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFloat::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsFloat();
    $this->assertInstanceOf(PapayaFilterFloat::class, $profile->getFilter());
  }
}
