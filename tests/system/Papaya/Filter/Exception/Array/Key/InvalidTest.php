<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterExceptionArrayKeyInvalidTest extends PapayaTestCase {

  public function testConstructor() {
    $exception = new PapayaFilterExceptionArrayKeyInvalid('foo');
    $this->assertEquals('Invalid key "foo" in array.', $exception->getMessage());
  }
}
