<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterEqualsParameterTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterEqualsParameter::__construct
  */
  public function testConstructor() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    $filter = new PapayaFilterEqualsParameter($parameters, 'foo');
    $this->assertAttributeSame($parameters, '_parameters', $filter);
    $this->assertAttributeEquals(new PapayaRequestParametersName('foo'), '_parameterName', $filter);
  }

  /**
   * @covers PapayaFilterEqualsParameter::validate
   */
  public function testValidateTrue() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    $filter = new PapayaFilterEqualsParameter($parameters, 'foo');
    $this->assertTrue($filter->validate('bar'));
  }

  /**
   * @covers PapayaFilterEqualsParameter::validate
   */
  public function testValidateInvalidFilterException() {
    $parameters = new PapayaRequestParameters(array('foo' => 'booo'));
    $filter = new PapayaFilterEqualsParameter($parameters, 'foo');
    $this->setExpectedException('PapayaFilterExceptionInvalid', 'Invalid value "bar"');
    $filter->validate('bar');
  }

  /**
   * @covers PapayaFilterEqualsParameter::filter
   */
  public function testFilterIsNull() {
    $parameters = new PapayaRequestParameters(array());
    $filter = new PapayaFilterEqualsParameter($parameters, 'foo');
    $this->assertNull($filter->filter('foo3'));
  }

  /**
   * @covers PapayaFilterEqualsParameter::filter
   */
  public function testFilterExpectingValue() {
    $parameters = new PapayaRequestParameters(array('foo' => 'bar'));
    $filter = new PapayaFilterEqualsParameter($parameters, 'foo');
    $this->assertEquals('bar', $filter->filter('bar'));
  }
}