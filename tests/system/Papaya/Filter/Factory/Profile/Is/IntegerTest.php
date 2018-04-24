<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsIntegerTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsInteger::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsInteger();
    $this->assertInstanceOf(PapayaFilterInteger::class, $profile->getFilter());
  }
}
