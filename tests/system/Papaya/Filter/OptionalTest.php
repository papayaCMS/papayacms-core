<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterOptionalTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterOptional::__construct
  * @covers PapayaFilterOptional::getInnerFilter
  */
  public function testConstructor() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->assertSame(
     $innerFilter, $filter->getInnerFilter()
    );
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  */
  public function testGetFilterCachesCreatedFilter() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $actualFilter = $filter->getFilter();
    $this->assertSame($actualFilter, $filter->getFilter());
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  * @covers PapayaFilterOptional::validate
  */
  public function testValidateWithEmptyValue() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->assertTrue(
      $filter->validate('')
    );
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  * @covers PapayaFilterOptional::validate
  */
  public function testValidateWithValidValue() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->assertTrue(
      $filter->validate('42')
    );
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  * @covers PapayaFilterOptional::validate
  */
  public function testValidateWithInvalidValueExpectingException() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->setExpectedException('PapayaFilterExceptionRangeMaximum');
    $this->assertTrue(
      $filter->validate('84')
    );
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  * @covers PapayaFilterOptional::filter
  */
  public function testFilterWithEmptyValue() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->assertNull(
      $filter->filter('')
    );
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  * @covers PapayaFilterOptional::validate
  */
  public function testFilterWithValidValue() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->assertSame(
      42, $filter->filter('42')
    );
  }

  /**
  * @covers PapayaFilterOptional::getFilter
  * @covers PapayaFilterOptional::filter
  */
  public function testFilterWithInvalidValueExpectingNull() {
    $filter = new PapayaFilterOptional($innerFilter = new PapayaFilterInteger(21, 42));
    $this->assertNull(
      $filter->filter('84')
    );
  }
}
