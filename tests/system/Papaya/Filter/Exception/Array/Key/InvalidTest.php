<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaFilterExceptionArrayKeyInvalidTest extends PapayaTestCase {

  public function testConstructor() {
    $exception = new PapayaFilterExceptionArrayKeyInvalid('foo');
    $this->assertEquals('Invalid key "foo" in array.', $exception->getMessage());
  }
}
