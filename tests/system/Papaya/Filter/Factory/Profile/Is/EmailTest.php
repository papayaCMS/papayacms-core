<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterFactoryProfileIsEmailTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsEmail::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileIsEmail();
    $this->assertInstanceOf('PapayaFilterEmail', $profile->getFilter());
  }
}
