<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsGermanZipTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGermanZip::getFilter
   * @dataProvider provideValidZips
   */
  public function testGetFilterExpectTrue($zip) {
    $profile = new PapayaFilterFactoryProfileIsGermanZip();
    $this->assertTrue($profile->getFilter()->validate($zip));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsGermanZip::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsGermanZip();
    $this->expectException(PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }

  public static function provideValidZips() {
    return array(
      array('50670'),
      array('D-50670')
    );
  }
}
