<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterNotTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterNot::__construct
  */
  public function testConstructor() {
    $filterMock = $this->getMock('PapayaFilter');
    $filter = new PapayaFilterNot($filterMock);
    $this->assertAttributeInstanceOf(
      'PapayaFilter', '_filter', $filter
    );
  }

  /**
  * @covers PapayaFilterNot::validate
  * @expectedException PapayaFilterException
  */
  public function testValidateExpectingException() {
    $filterMock = $this->getMock('PapayaFilter');
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
    $filterMock = $this->getMock('PapayaFilter');
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
    $filterMock = $this->getMock('PapayaFilter');
    $filter = new PapayaFilterNot($filterMock);
    $this->assertEquals('Test', $filter->filter('Test'));
  }

  /*************************************
  * Callbacks
  *************************************/

  public function callbackThrowFilterException() {
    throw $this->getMock('PapayaFilterException', array(), array('Test Exception'));
  }

}
