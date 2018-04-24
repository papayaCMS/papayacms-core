<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsPasswordTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsPassword::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsPassword();
    $this->assertInstanceOf(PapayaFilterPassword::class, $profile->getFilter());
  }
}
