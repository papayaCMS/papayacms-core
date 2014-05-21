<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsPasswordTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsPassword::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsPassword();
    $this->assertInstanceOf('PapayaFilterPassword', $profile->getFilter());
  }
}
