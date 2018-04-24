<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterLocaleGermanyZipTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterLocaleGermanyZip::__construct
  * @dataProvider providerConstructor
  */
  public function testConstruct($value) {
    $filter = new PapayaFilterLocaleGermanyZip($value);
    $this->assertAttributeEquals($value, '_allowCountryPrefix', $filter);
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::__construct
  */
  public function testConstructWithoutArgument() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->assertAttributeEquals(NULL, '_allowCountryPrefix', $filter);
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidate() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->assertTrue($filter->validate('12345'));
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectCharacterInvalidException() {
    $filter = new PapayaFilterLocaleGermanyZip(TRUE);
    $this->setExpectedException('PapayaFilterExceptionCharacterInvalid');
    $filter->validate('11235');
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectLengthMinimumException() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->setExpectedException('PapayaFilterExceptionLengthMinimum');
    $filter->validate('123');
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectLengthMaximumException() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->setExpectedException('PapayaFilterExceptionLengthMaximum');
    $filter->validate('342423432424');
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::validate
  */
  public function testValidateExpectCharacterInvalidExceptionInPostalcode() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->setExpectedException('PapayaFilterExceptionCharacterInvalid');
    $filter->validate('23a91');
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::filter
  */
  public function testFilter() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->assertEquals('12345', $filter->filter('12345'));
  }

  /**
  * @covers PapayaFilterLocaleGermanyZip::filter
  */
  public function testFilterExpectsFilterException() {
    $filter = new PapayaFilterLocaleGermanyZip();
    $this->assertNull($filter->filter('78asdblnnlnltest'));
  }

  /************************
  * Data Provider
  ************************/

  public static function providerConstructor() {
    return array(
      array(NULL),
      array(TRUE),
      array(FALSE)
    );
  }

}
