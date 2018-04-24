<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCallback::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCallback_TestProxy('', 'function');
    $this->assertAttributeEquals(
      'function', '_callback', $e
    );
  }

  /**
  * @covers PapayaFilterExceptionCallback::getCallback
  */
  public function testGetCallback() {
    $e = new PapayaFilterExceptionCallback_TestProxy('', 'function');
    $this->assertEquals(
      'function', $e->getCallback()
    );
  }

  /**
  * @covers PapayaFilterExceptionCallback::callbackToString
  * @dataProvider provideCallbacks
  */
  public function testCallbackToString($expected, $callback) {
    $e = new PapayaFilterExceptionCallback_TestProxy('', $callback);
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
        array(new PapayaFilterExceptionCallback_SampleCallback(), 'sample')
      ),
      array(
        'PapayaFilterExceptionCallback_SampleCallback::sample',
        array(PapayaFilterExceptionCallback_SampleCallback::class, 'sample')
      )
    );
  }
}

class  PapayaFilterExceptionCallback_SampleCallback {
  public function sample() {
  }
}

class PapayaFilterExceptionCallback_TestProxy extends PapayaFilterExceptionCallback {
  public function callbackToString($callback) {
    return parent::callbackToString($callback);
  }
}
