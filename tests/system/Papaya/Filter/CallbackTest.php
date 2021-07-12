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

  /**
   * @covers \Papaya\Filter\Callback
   */
  class CallbackTest extends \Papaya\TestFramework\TestCase {

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

    public function testValidateWithInvalidCallbackExpectingException() {
      $filter = new Callback('INVALID_CALLBACK_NAME');
      $this->expectException(Exception\InvalidCallback::class);
      $filter->validate('bar');
    }

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
