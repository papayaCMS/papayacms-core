<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsTextWithNumbersTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsTextWithNumbers::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsTextWithNumbers();
    $this->assertTrue($profile->getFilter()->validate('Hallo 1. Welt!'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsTextWithNumbers::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsTextWithNumbers();
    $this->expectException(PapayaFilterException::class);
    $profile->getFilter()->validate('');
  }
}
