<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterFactoryProfileRegexTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileRegex::getFilter
   */
  public function testGetFilter() {
    $profile = new PapayaFilterFactoryProfileRegex();
    $profile->options('(^pattern$)D');
    $filter = $profile->getFilter();
    $this->assertInstanceOf(PapayaFilterPcre::class, $filter);
    $this->assertTrue($filter->validate('pattern'));
  }
}
