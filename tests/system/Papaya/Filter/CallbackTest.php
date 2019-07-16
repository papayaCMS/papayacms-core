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

namespace Papaya\Filter {

  require_once __DIR__.'/../../../bootstrap.php';

  class CallbackTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Filter\Callback::__construct
     */
    public function testConstructor() {
      $filter = new Callback('CallbackTest_ValidateCallback');
      $this->assertAttributeEquals(
        'CallbackTest_ValidateCallback', '_callback', $filter
      );
    }

    /**
     * @covers \Papaya\Filter\Callback::__construct
     */
    public function testConstructorWithArgumentsArray() {
      $filter = new Callback(
        'CallbackTest_ValidateCallback', array('test')
      );
      $this->assertAttributeEquals(
        array('test'), '_arguments', $filter
      );
    }

    /**
     * @covers \Papaya\Filter\Callback::validate
     */
    public function testValidateExpectingTrue() {
      $filter = new Callback(
        function($value, $pattern) {
          return preg_match($pattern, $value);
        },
        array('(^foo$)')
      );
      $this->assertTrue(
        $filter->validate('foo')
      );
    }

    /**
     * @covers \Papaya\Filter\Callback::validate
     */
    public function testValidateWithInvalidCallbackExpectingException() {
      $filter = new Callback('INVALID_CALLBACK_NAME');
      $this->expectException(Exception\InvalidCallback::class);
      $filter->validate('bar');
    }

    /**
     * @covers \Papaya\Filter\Callback::validate
     */
    public function testValidateWithInvalidValueExpectingException() {
      $filter = new Callback(
        function($value, $pattern) {
          return preg_match($pattern, $value);
        },
        array('(^foo$)')
      );
      $this->expectException(Exception\FailedCallback::class);
      $filter->validate('bar');
    }

    /**
     * @covers \Papaya\Filter\Callback::filter
     */
    public function testFilterExpectingTrue() {
      $filter = new Callback(
        function($value, $pattern) {
          return preg_match($pattern, $value);
        },
        array('(^foo$)')
      );
      $this->assertEquals(
        'foo', $filter->filter('foo')
      );
    }

    /**
     * @covers \Papaya\Filter\Callback::filter
     */
    public function testFilterExpectingNull() {
      $filter = new Callback(
        function($value, $pattern) {
          return preg_match($pattern, $value);
        },
        array('(^foo$)')
      );
      $this->assertNull(
        $filter->filter('bar')
      );
    }
  }
}
