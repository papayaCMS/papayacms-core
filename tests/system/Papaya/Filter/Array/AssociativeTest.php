<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterArrayAssociativeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterArrayAssociative
   */
  public function testValidateExpectingTrue() {
    $subFilter = $this->getMockBuilder('PapayaFilter')->getMock();
    $subFilter
      ->expects($this->any())
      ->method('validate')
      ->willReturn(TRUE);
    $filter = new PapayaFilterArrayAssociative(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertTrue($filter->validate(['foo' => 21, 'bar' => 42]));
  }

  /**
   * @covers PapayaFilterArrayAssociative
   */
  public function testValidateInvalidElementValueExpectingException() {
    $subFilter = $this->getMockBuilder('PapayaFilter')->getMock();
    $subFilter
      ->expects($this->any())
      ->method('validate')
      ->willThrowException($this->getMockBuilder('PapayaFilterException')->getMock());
    $filter = new PapayaFilterArrayAssociative(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->setExpectedException('PapayaFilterException');
    $this->assertTrue($filter->validate(['foo' => 21, 'bar' => 42]));
  }

  /**
   * @covers PapayaFilterArrayAssociative
   */
  public function testValidateInvalidKeyExpectingException() {
    $subFilter = $this->getMockBuilder('PapayaFilter')->getMock();
    $subFilter
      ->expects($this->any())
      ->method('validate')
      ->willReturn(TRUE);
    $filter = new PapayaFilterArrayAssociative(
      [
        'foo' => $subFilter
      ]
    );
    $this->setExpectedException('PapayaFilterExceptionArrayKeyInvalid');
    $this->assertTrue($filter->validate(['foo' => 21, 'bar' => 42]));
  }

  /**
   * @covers PapayaFilterArrayAssociative
   */
  public function testFilterExpectingValue() {
    $subFilter = $this->getMockBuilder('PapayaFilter')->getMock();
    $subFilter
      ->expects($this->any())
      ->method('filter')
      ->willReturnArgument(0);
    $filter = new PapayaFilterArrayAssociative(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertEquals(
      ['foo' => 21, 'bar' => 42],
      $filter->filter(['foo' => 21, 'bar' => 42])
    );
  }

  /**
   * @covers PapayaFilterArrayAssociative
   */
  public function testFilterExpectingNull() {
    $subFilter = $this->getMockBuilder('PapayaFilter')->getMock();
    $subFilter
      ->expects($this->any())
      ->method('filter')
      ->willReturn(NULL);
    $filter = new PapayaFilterArrayAssociative(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertEquals(
      [],
      $filter->filter(['foo' => 21, 'bar' => 42])
    );
  }

  /**
   * @covers PapayaFilterArrayAssociative
   */
  public function testFilterWithoutArrayExpectingNull() {
    $subFilter = $this->getMockBuilder('PapayaFilter')->getMock();
    $subFilter
      ->expects($this->any())
      ->method('filter')
      ->willReturn(TRUE);
    $filter = new PapayaFilterArrayAssociative(
      [
        'foo' => $subFilter,
        'bar' => $subFilter
      ]
    );
    $this->assertNull(
      $filter->filter(42)
    );
  }

}
