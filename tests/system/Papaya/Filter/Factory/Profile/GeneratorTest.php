<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterFactoryProfileGeneratorTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileGenerator::getFilter
   */
  public function testGetFilterWithIntegerMinAndMax() {
    $profile = new PapayaFilterFactoryProfileGenerator();
    $profile->options(array(PapayaFilterInteger::class, 1, 42));
    $filter = $profile->getFilter();
    $this->assertInstanceOf(PapayaFilterInteger::class, $filter);
    $this->assertTrue($filter->validate('21'));
  }

  /**
   * @covers PapayaFilterFactoryProfileGenerator::getFilter
   */
  public function testGetFilterWithInvalidOptionsExpectingException() {
    $profile = new PapayaFilterFactoryProfileGenerator();
    $profile->options(NULL);
    $this->setExpectedException(PapayaFilterFactoryExceptionInvalidOptions::class);
    $profile->getFilter();
  }

  /**
   * @covers PapayaFilterFactoryProfileGenerator::getFilter
   */
  public function testGetFilterWithInvalidFilterClass() {
    $profile = new PapayaFilterFactoryProfileGenerator();
    $profile->options(array('stdClass'));
    $this->setExpectedException(PapayaFilterFactoryExceptionInvalidFilter::class);
    $profile->getFilter();
  }
}
