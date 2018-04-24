<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsIpAddressV6Test extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddressV6::getFilter
   */
  public function testGetFilterWithIpV6AddressExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsIpAddressV6();
    $this->assertTrue($profile->getFilter()->validate('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsIpAddressV6::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsIpAddressV6();
    $this->setExpectedException(PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }
}
