<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsGuidTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGuid::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsGuid();
    $this->assertTrue($profile->getFilter()->validate('ab123456789012345678901234567890'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsGuid::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsGuid();
    $this->expectException(PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }
}
