<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsIsoDateTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsIsoDate::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsIsoDate();
    $this->assertTrue($profile->getFilter()->validate('2012-08-15'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIsoDate::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsIsoDate();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
