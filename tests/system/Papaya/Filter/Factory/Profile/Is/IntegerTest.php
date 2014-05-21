<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsIntegerTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsInteger::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsInteger();
    $this->assertInstanceOf('PapayaFilterInteger', $profile->getFilter());
  }
}
