<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterXmlTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterXml::__construct
   */
  public function testConstructorWihtAllArguments() {
    $filter = new PapayaFilterXml(FALSE);
    $this->assertAttributeEquals(
      FALSE, '_allowFragments', $filter
    );
  }

  /**
   * @covers PapayaFilterXml::validate
   * @dataProvider provideValidXmlFragments
   */
  public function testValidate($fragment) {
    $filter = new PapayaFilterXml();
    $this->assertTrue($filter->validate($fragment));
  }

  /**
   * @covers PapayaFilterXml::validate
   */
  public function testValidateWithDocument() {
    $filter = new PapayaFilterXml(FALSE);
    $this->assertTrue($filter->validate('<html/>'));
  }

  /**
   * @covers PapayaFilterXml::validate
   * @dataProvider provideInvalidXmlFragments
   */
  public function testValidateExpectingException($fragment) {
    $filter = new PapayaFilterXml();
    $this->setExpectedException(PapayaFilterExceptionXml::class);
    $filter->validate($fragment);
  }

  /**
   * @covers PapayaFilterXml::validate
   */
  public function testValidateWithEmptyStringExpectingException() {
    $filter = new PapayaFilterXml();
    $this->setExpectedException(PapayaFilterExceptionEmpty::class);
    $filter->validate('');
  }

  /**
   * @covers PapayaFilterXml::validate
   */
  public function testValidateWithDocumentExpectingException() {
    $filter = new PapayaFilterXml(FALSE);
    $this->setExpectedException(PapayaFilterExceptionXml::class);
    $filter->validate('TEXT');
  }

  /**
   * @covers PapayaFilterXml::filter
   * @dataProvider provideValidXmlFragments
   */
  public function testFilter($fragment) {
    $filter = new PapayaFilterXml();
    $this->assertEquals($fragment, $filter->filter($fragment));
  }

  /**
   * @covers PapayaFilterXml::filter
   * @dataProvider provideInvalidXmlFragments
   */
  public function testFilterExpectingNull($fragment) {
    $filter = new PapayaFilterXml();
    $this->assertNull($filter->filter($fragment));
  }

  public static function provideValidXmlFragments() {
    return array(
      array('<p>Test</p>'),
      array('<p>Test</p><p>Test</p>'),
      array('Test')
    );
  }

  public static function provideInvalidXmlFragments() {
    return array(
      array('<p>Test'),
      array('Test</p>'),
      array('>Test<'),
      array('<Test<')
    );
  }
}
