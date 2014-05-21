<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsCssColorTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsCssColor::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsCssColor();
    $this->assertInstanceOf('PapayaFilterColor', $profile->getFilter());
  }
}
