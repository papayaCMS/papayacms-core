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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionCallbackTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\Exception\Callback::__construct
  */
  public function testConstructor() {
    $e = new \PapayaFilterExceptionCallback_TestProxy('', 'function');
    $this->assertAttributeEquals(
      'function', '_callback', $e
    );
  }

  /**
  * @covers \Papaya\Filter\Exception\Callback::getCallback
  */
  public function testGetCallback() {
    $e = new \PapayaFilterExceptionCallback_TestProxy('', 'function');
    $this->assertEquals(
      'function', $e->getCallback()
    );
  }

  /**
   * @covers \Papaya\Filter\Exception\Callback::callbackToString
   * @dataProvider provideCallbacks
   * @param string $expected
   * @param callable $callback
   */
  public function testCallbackToString($expected, $callback) {
    $e = new \PapayaFilterExceptionCallback_TestProxy('', $callback);
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
      array('function() {...}', function() {}),
      array(
        'PapayaFilterExceptionCallback_SampleCallback->sample',
        array(new \PapayaFilterExceptionCallback_SampleCallback(), 'sample')
      ),
      array(
        'PapayaFilterExceptionCallback_SampleCallback::sample',
        array(\PapayaFilterExceptionCallback_SampleCallback::class, 'sample')
      )
    );
  }
}

class PapayaFilterExceptionCallback_SampleCallback {
  public function sample() {
  }
}

class PapayaFilterExceptionCallback_TestProxy extends \Papaya\Filter\Exception\Callback {
  public function callbackToString($callback) {
    return parent::callbackToString($callback);
  }
}
