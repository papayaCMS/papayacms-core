<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterStringExplodeTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testValidateWithSingleTokenExpectingTrue() {
    $filter = new PapayaFilterStringExplode();
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testValidateWithSeveralTokensExpectingTrue() {
    $filter = new PapayaFilterStringExplode();
    $this->assertTrue(
      $filter->validate('foo, bar, 42')
    );
  }

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testValidateWithIntegerFilterExpectingTrue() {
    $filter = new PapayaFilterStringExplode(',', new PapayaFilterInteger());
    $this->assertTrue(
      $filter->validate('42')
    );
  }

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testValidateWithEmptyValueExpectingException() {
    $filter = new PapayaFilterStringExplode(',', new PapayaFilterInteger());
    $this->setExpectedException('PapayaFilterExceptionEmpty');
    $filter->validate('');
  }

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testFilterWithSingleToken() {
    $filter = new PapayaFilterStringExplode();
    $this->assertEquals(
      ['foo'],
      $filter->filter('foo')
    );
  }

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testFilterWithSeveralTokens() {
    $filter = new PapayaFilterStringExplode();
    $this->assertSame(
      ['foo', 'bar', '42'],
      $filter->filter('foo, bar, 42')
    );
  }

  /**
   * @covers PapayaFilterStringExplode
   */
  public function testFilterWithIntegerElementFilter() {
    $filter = new PapayaFilterStringExplode(',', new PapayaFilterInteger());
    $this->assertSame(
      [42],
      $filter->filter('42')
    );
  }

}