<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsFloatTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsFloat::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsFloat();
    $this->assertInstanceOf('PapayaFilterFloat', $profile->getFilter());
  }
}
