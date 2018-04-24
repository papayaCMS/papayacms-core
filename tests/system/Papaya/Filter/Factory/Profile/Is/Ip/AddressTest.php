<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsIpAddressTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddress::getFilter
   */
  public function testGetFilterWithIpV4AddressExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsIpAddress();
    $this->assertTrue($profile->getFilter()->validate('127.0.0.1'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddress::getFilter
   */
  public function testGetFilterWithIpV6AddressExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsIpAddress();
    $this->assertTrue($profile->getFilter()->validate('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddress::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsIpAddress();
    $this->setExpectedException(PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }
}
