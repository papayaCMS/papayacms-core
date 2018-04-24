<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterNotTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterNot::__construct
  */
  public function testConstructor() {
    $filterMock = $this->createMock(PapayaFilter::class);
    $filter = new PapayaFilterNot($filterMock);
    $this->assertAttributeInstanceOf(
      PapayaFilter::class, '_filter', $filter
    );
  }

  /**
  * @covers PapayaFilterNot::validate
  * @expectedException PapayaFilterException
  */
  public function testValidateExpectingException() {
    $filterMock = $this->createMock(PapayaFilter::class);
    $filterMock
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo(123))
      ->will($this->returnValue(TRUE));
    $filter = new PapayaFilterNot($filterMock);
    $filter->validate(123);
  }

  /**
  * @covers PapayaFilterNot::validate
  */
  public function testValidateExpectingTrue() {
    $filterMock = $this->createMock(PapayaFilter::class);
    $filterMock
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('abc'))
      ->will($this->returnCallback(array($this, 'callbackThrowFilterException')));
    $filter = new PapayaFilterNot($filterMock);
    $this->assertTrue($filter->validate('abc'));
  }

  /**
   * @covers PapayaFilterNot::filter
   */
  public function testFilter() {
    $filterMock = $this->createMock(PapayaFilter::class);
    $filter = new PapayaFilterNot($filterMock);
    $this->assertEquals('Test', $filter->filter('Test'));
  }

  /*************************************
  * Callbacks
  *************************************/

  public function callbackThrowFilterException() {
    throw $this->getMock(PapayaFilterException::class, array(), array('Test Exception'));
  }

}
