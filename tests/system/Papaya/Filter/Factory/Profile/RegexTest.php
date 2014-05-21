<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaFilterFactoryProfileRegexTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileRegex::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileRegex();
    $profile->options('(^pattern$)D');
    $filter = $profile->getFilter();
    $this->assertInstanceOf('PapayaFilterPcre', $filter);
    $this->assertTrue($filter->validate('pattern'));
  }
}