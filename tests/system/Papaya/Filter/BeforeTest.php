<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterBeforeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterBefore
   */
  public function testValidate() {
    $before = $this->getMockBuilder(PapayaFilter::class)->getMock();
    $before
      ->expects($this->once())
      ->method('filter')
      ->with('foo')
      ->willReturn('success');

    $after = $this->getMockBuilder(PapayaFilter::class)->getMock();
    $after
      ->expects($this->once())
      ->method('validate')
      ->with('success')
      ->willReturn(TRUE);

    $filter = new PapayaFilterBefore($before, $after);
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers PapayaFilterBefore
   */
  public function testFilter() {
    $before = $this->getMockBuilder(PapayaFilter::class)->getMock();
    $before
      ->expects($this->once())
      ->method('filter')
      ->with('foo')
      ->willReturn('success');

    $after = $this->getMockBuilder(PapayaFilter::class)->getMock();
    $after
      ->expects($this->once())
      ->method('filter')
      ->with('success')
      ->willReturn(42);

    $filter = new PapayaFilterBefore($before, $after);
    $this->assertSame(
      42,
      $filter->filter('foo')
    );
  }
}
