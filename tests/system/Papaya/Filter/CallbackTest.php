<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../bootstrap.php';

class PapayaFilterCallbackTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterCallback::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaFilterCallback('PapayaFilterCallbackTest_ValidateCallback');
    $this->assertAttributeEquals(
      'PapayaFilterCallbackTest_ValidateCallback', '_callback', $filter
    );
  }

  /**
  * @covers \PapayaFilterCallback::__construct
  */
  public function testConstructorWithArgumentsArray() {
    $filter = new \PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('test')
    );
    $this->assertAttributeEquals(
      array('test'), '_arguments', $filter
    );
  }

  /**
  * @covers \PapayaFilterCallback::validate
  * @covers \PapayaFilterCallback::_isCallback
  */
  public function testValidateExpectingTrue() {
    $filter = new \PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \PapayaFilterCallback::validate
  * @covers \PapayaFilterCallback::_isCallback
  */
  public function testValidateWithInvalidCallbackExpectingException() {
    $filter = new \PapayaFilterCallback('INVALID_CALLBACK_NAME');
    $this->expectException(PapayaFilterExceptionCallbackInvalid::class);
    $filter->validate('bar');
  }

  /**
  * @covers \PapayaFilterCallback::validate
  */
  public function testValidateWithInvalidValueExpectingException() {
    $filter = new \PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->expectException(PapayaFilterExceptionCallbackFailed::class);
    $filter->validate('bar');
  }

  /**
  * @covers \PapayaFilterCallback::filter
  */
  public function testFilterExpectingTrue() {
    $filter = new \PapayaFilterCallback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
  * @covers \PapayaFilterCallback::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \PapayaFilterCallback(
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
