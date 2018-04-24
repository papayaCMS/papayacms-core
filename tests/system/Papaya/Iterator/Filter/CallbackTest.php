<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorFilterCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorFilterCallback::__construct
  * @covers PapayaIteratorFilterCallback::setCallback
  * @covers PapayaIteratorFilterCallback::getCallback
  */
  public function testConstructor() {
    $filter = new PapayaIteratorFilterCallback(
      new EmptyIterator(), array($this,  'callbackAssertInteger')
    );
    $this->assertEquals(
      array($this,  'callbackAssertInteger'), $filter->getCallback()
    );
  }

  /**
  * @covers PapayaIteratorFilterCallback::setCallback
  */
  public function testSetCallbackWithInvalidCallbackExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    $filter = new PapayaIteratorFilterCallback(new EmptyIterator(), NULL);
  }

  /**
  * @covers PapayaIteratorFilterCallback::accept
  */
  public function testAccept() {
    $data = array(
      'ok' => 42,
      'fail' => 'wrong'
    );
    $filter = new PapayaIteratorFilterCallback(
      new ArrayIterator($data), array($this,  'callbackAssertInteger')
    );
    $this->assertEquals(
      array('ok' => 42),
      iterator_to_array($filter, TRUE)
    );
  }

  public function callbackAssertInteger($element, $key) {
    return is_int($element);
  }
}
