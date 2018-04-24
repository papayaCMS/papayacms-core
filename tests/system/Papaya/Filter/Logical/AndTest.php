<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterLogicalAndTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterLogicalAnd::validate
  */
  public function testValidateExpectingTrue() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $subFilterTwo = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterTwo
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers PapayaFilterLogicalAnd::validate
  */
  public function testValidateWithScalarValuesExpectingTrue() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new PapayaFilterLogicalAnd($subFilterOne, 'foo');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers PapayaFilterLogicalAnd::validate
  */
  public function testValidateWithScalarValuesExpectingException() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterOne
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(TRUE));
    $filter = new PapayaFilterLogicalAnd($subFilterOne, 'bar');
    $this->setExpectedException(PapayaFilterException::class);
    $filter->validate('foo');
  }

  /**
  * @covers PapayaFilterLogicalAnd::filter
  */
  public function testFilter() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $subFilterTwo = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $filter = new PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertEquals(
      'foo',
      $filter->filter('foo')
    );
  }

  /**
  * @covers PapayaFilterLogicalAnd::filter
  */
  public function testFilterExpectingNullFromFirstSubFilter() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $subFilterTwo = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterTwo
      ->expects($this->never())
      ->method('filter');
    $filter = new PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }

  /**
  * @covers PapayaFilterLogicalAnd::filter
  */
  public function testFilterExpectingNullFromSecondSubFilter() {
    $subFilterOne = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterOne
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue('foo'));
    $subFilterTwo = $this->getMock(PapayaFilter::class, array('validate', 'filter'));
    $subFilterTwo
      ->expects($this->once())
      ->method('filter')
      ->with($this->equalTo('foo'))
      ->will($this->returnValue(NULL));
    $filter = new PapayaFilterLogicalAnd($subFilterOne, $subFilterTwo);
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
