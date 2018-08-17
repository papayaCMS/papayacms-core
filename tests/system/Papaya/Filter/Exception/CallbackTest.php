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

namespace Papaya\Filter\Exception {

  require_once __DIR__.'/../../../../bootstrap.php';

  class CallbackTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Filter\Exception\Callback::__construct
     */
    public function testConstructor() {
      $e = new Callback_TestProxy('', 'function');
      $this->assertAttributeEquals(
        'function', '_callback', $e
      );
    }

    /**
     * @covers \Papaya\Filter\Exception\Callback::getCallback
     */
    public function testGetCallback() {
      $e = new Callback_TestProxy('', 'function');
      $this->assertEquals(
        'function', $e->getCallback()
      );
    }

    /**
     * @covers       \Papaya\Filter\Exception\Callback::callbackToString
     * @dataProvider provideCallbacks
     * @param string $expected
     * @param callable $callback
     */
    public function testCallbackToString($expected, $callback) {
      $e = new Callback_TestProxy('', $callback);
      $this->assertEquals(
        $expected, $e->callbackToString($callback)
      );
    }

    /**************************
     * Data Provider
     **************************/

    public static function provideCallbacks() {
      return array(
        array('strpos', 'strpos'),
        array('function() {...}', function () {
        }),
        array(
          'Papaya\Filter\Exception\SampleCallback->sample',
          array(new SampleCallback(), 'sample')
        ),
        array(
          'Papaya\Filter\Exception\SampleCallback::sample',
          array(SampleCallback::class, 'sample')
        )
      );
    }
  }

  class SampleCallback {
    public function sample() {
    }
  }

  class Callback_TestProxy extends Callback {
    public function callbackToString($callback) {
      return parent::callbackToString($callback);
    }
  }
}
