<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaConfigurationIteratorTest extends PapayaTestCase {

  public function testIterator() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaConfiguration $configuration */
    $configuration = $this
      ->getMockBuilder(PapayaConfiguration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $configuration
      ->expects($this->any())
      ->method('get')
      ->will($this->returnValue(42));
    $iterator = new PapayaConfigurationIterator(array('SAMPLE_INT'), $configuration);
    $result = iterator_to_array($iterator);
    $this->assertEquals(
      array('SAMPLE_INT' => 42),
      $result
    );
  }

}
