<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterCallback::__construct
  */
  public function testConstructor() {
    $filter = new PapayaFilterCallback('PapayaFilterCallbackTest_ValidateCallback');
    $this->assertAttributeEquals(
      'PapayaFilterCallbackTest_ValidateCallback', '_callback', $filter
    );
  }

  /**
  * @covers PapayaFilterCallback::__construct
  */
  public function testConstructorWithArgumentsArray() {
    $filter = new PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('test')
    );
    $this->assertAttributeEquals(
      array('test'), '_arguments', $filter
    );
  }

  /**
  * @covers PapayaFilterCallback::validate
  * @covers PapayaFilterCallback::_isCallback
  */
  public function testValidateExpectingTrue() {
    $filter = new PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers PapayaFilterCallback::validate
  * @covers PapayaFilterCallback::_isCallback
  */
  public function testValidateWithInvalidCallbackExpectingException() {
    $filter = new PapayaFilterCallback('INVALID_CALLBACK_NAME');
    $this->setExpectedException('PapayaFilterExceptionCallbackInvalid');
    $filter->validate('bar');
  }

  /**
  * @covers PapayaFilterCallback::validate
  */
  public function testValidateWithInvalidValueExpectingException() {
    $filter = new PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->setExpectedException('PapayaFilterExceptionCallbackFailed');
    $filter->validate('bar');
  }

  /**
  * @covers PapayaFilterCallback::filter
  */
  public function testFilterExpectingTrue() {
    $filter = new PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
  * @covers PapayaFilterCallback::filter
  */
  public function testFilterExpectingNull() {
    $filter = new PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertNull(
      $filter->filter('bar')
    );
  }
}

function PapayaFilterCallbackTest_ValidateCallback($value, $pattern) {
  return preg_match($pattern, $value);
}
