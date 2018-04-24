<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsUrlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsUrl::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsUrl();
    $this->assertTrue($profile->getFilter()->validate('http://sample.tld/path/file.html?foo=bar'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsUrl::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsUrl();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate('foo');
  }
}
