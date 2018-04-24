<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsTimeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsTime::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsTime();
    $this->assertTrue($profile->getFilter()->validate('23:54'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsTime::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsTime();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
