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

class PapayaFilterCallbackTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\Callback::__construct
  */
  public function testConstructor() {
    $filter = new \Papaya\Filter\Callback('PapayaFilterCallbackTest_ValidateCallback');
    $this->assertAttributeEquals(
      'PapayaFilterCallbackTest_ValidateCallback', '_callback', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Callback::__construct
  */
  public function testConstructorWithArgumentsArray() {
    $filter = new \Papaya\Filter\Callback(
      'PapayaFilterCallbackTest_ValidateCallback', array('test')
    );
    $this->assertAttributeEquals(
      array('test'), '_arguments', $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Callback::validate
  * @covers \Papaya\Filter\Callback::_isCallback
  */
  public function testValidateExpectingTrue() {
    $filter = new \Papaya\Filter\Callback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertTrue(
      $filter->validate('foo')
    );
  }

  /**
  * @covers \Papaya\Filter\Callback::validate
  * @covers \Papaya\Filter\Callback::_isCallback
  */
  public function testValidateWithInvalidCallbackExpectingException() {
    $filter = new \Papaya\Filter\Callback('INVALID_CALLBACK_NAME');
    $this->expectException(\Papaya\Filter\Exception\InvalidCallback::class);
    $filter->validate('bar');
  }

  /**
  * @covers \Papaya\Filter\Callback::validate
  */
  public function testValidateWithInvalidValueExpectingException() {
    $filter = new \Papaya\Filter\Callback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->expectException(\Papaya\Filter\Exception\FailedCallback::class);
    $filter->validate('bar');
  }

  /**
  * @covers \Papaya\Filter\Callback::filter
  */
  public function testFilterExpectingTrue() {
    $filter = new \Papaya\Filter\Callback(
      'PapayaFilterCallbackTest_ValidateCallback', array('(^foo$)')
    );
    $this->assertEquals(
      'foo', $filter->filter('foo')
    );
  }

  /**
  * @covers \Papaya\Filter\Callback::filter
  */
  public function testFilterExpectingNull() {
    $filter = new \Papaya\Filter\Callback(
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
