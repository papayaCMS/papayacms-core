<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsServerAddressTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsServerAddress::getFilter
   * @dataProvider provideServerAddressStrings
   */
  public function testGetFilterExpectTrue($string) {
    $profile = new PapayaFilterFactoryProfileIsServerAddress();
    $this->assertTrue($profile->getFilter()->validate($string));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsServerAddress::getFilter
   * @dataProvider provideInvalidStrings
   */
  public function testGetFilterExpectException($string) {
    $profile = new PapayaFilterFactoryProfileIsServerAddress();
    $this->setExpectedException('PapayaFilterException');
    $profile->getFilter()->validate($string);
  }

  public static function provideServerAddressStrings() {
    return array(
      array('localhost'),
      array('www.sample.tld:8080'),
      array('127.0.0.1')
    );
  }

  public static function provideInvalidStrings() {
    return array(
      array(''),
      array(' foo ')
    );
  }
}
