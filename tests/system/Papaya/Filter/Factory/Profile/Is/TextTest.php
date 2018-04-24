<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsTextTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsText::getFilter
   */
  public function testGetFilterExpectTrue() {
    $profile = new PapayaFilterFactoryProfileIsText();
    $this->assertTrue($profile->getFilter()->validate('Hallo Welt!'));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsText::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsText();
    $this->setExpectedException(PapayaFilterException::class);
    $profile->getFilter()->validate('123');
  }
}
