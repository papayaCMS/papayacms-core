<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterPcreTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterPcre::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterPcre('(foo)');
    $this->assertAttributeEquals(
      '(foo)', '_pattern', $filter
    );
  }

  /**
  * @covers PapayaFilterPcre::__construct
  */
  public function testConstructorWithSubMatch() {
    $filter = new PapayaFilterPcre('(foo)', 1);
    $this->assertAttributeEquals(
      1, '_subMatch', $filter
    );
  }

  /**
  * @covers PapayaFilterPcre::validate
  */
  public function testValidate() {
    $filter = new PapayaFilterPcre('(^foo$)');
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers PapayaFilterPcre::validate
  */
  public function testValidateExpectingException() {
    $filter = new PapayaFilterPcre('(^foo$)');
    $this->setExpectedException('PapayaFilterExceptionPcre');
    $filter->validate('bar');
  }

  /**
  * @covers PapayaFilterPcre::filter
  */
  public function testFilter() {
    $filter = new PapayaFilterPcre('(^foo$)');
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
  * @covers PapayaFilterPassword::filter
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterPcre('(^foo$)');
    $this->assertNull(
      $filter->filter('bar')
    );
  }

  /**
  * @covers PapayaFilterPcre::filter
  */
  public function testFilterExpectingSubMatch() {
    $filter = new PapayaFilterPcre('(^f(oo)$)', 1);
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
  * @covers PapayaFilterPcre::filter
  */
  public function testFilterExpectingNamedSubMatch() {
    $filter = new PapayaFilterPcre('(^f(?P<part>oo)$)', 'part');
    $this->assertEquals(
      'oo', $filter->filter('foo')
    );
  }

  /**
  * @covers PapayaFilterPcre::filter
  */
  public function testFilterWithInvalidSubMatchExpectingNull() {
    $filter = new PapayaFilterPcre('(^f(oo)$)', 'part');
    $this->assertNull(
      $filter->filter('foo')
    );
  }
}
