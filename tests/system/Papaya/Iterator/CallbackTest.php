<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaIteratorCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorCallback::__construct
  * @covers PapayaIteratorCallback::getInnerIterator
  */
  public function testConstructor() {
    $iterator = new PapayaIteratorCallback(
      $innerIterator = $this->getMock('Iterator'),
      array($this, 'callbackChangeValue')
    );
    $this->assertSame(
      $innerIterator, $iterator->getInnerIterator()
    );
  }

  /**
  * @covers PapayaIteratorCallback
  */
  public function testIteration() {
    $iterator = new PapayaIteratorCallback(
      new ArrayIterator(array(21, 42)),
      array($this, 'callbackChangeValue')
    );
    $this->assertEquals(
      array(
        0 => 'Key: 0, Value: 21',
        1 => 'Key: 1, Value: 42'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorCallback
  */
  public function testIterationWithKeys() {
    $iterator = new PapayaIteratorCallback(
      new ArrayIterator(array(21 => '50%', 42 => '100%')),
      array($this, 'callbackChangeValue')
    );
    $this->assertEquals(
      array(
        21 => 'Key: 21, Value: 50%',
        42 => 'Key: 42, Value: 100%'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorCallback
  */
  public function testIterationModifyKeys() {
    $iterator = new PapayaIteratorCallback(
      new ArrayIterator(array(21 => '50%', 42 => '100%')),
      array($this, 'callbackFlip'),
      PapayaIteratorCallback::MODIFY_KEYS
    );
    $this->assertEquals(
      array(
        '50%' => '50%',
        '100%' => '100%'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorCallback
  */
  public function testIterationModifyKeysAndValues() {
    $iterator = new PapayaIteratorCallback(
      new ArrayIterator(array(21 => '50%', 42 => '100%')),
      array($this, 'callbackFlip'),
      PapayaIteratorCallback::MODIFY_BOTH
    );
    $this->assertEquals(
      array(
        '50%' => 21,
        '100%' => 42
      ),
      iterator_to_array($iterator)
    );
  }

  public function callbackFlip($element, $key, $target) {
    return ($target == PapayaIteratorCallback::MODIFY_KEYS) ? $element : $key;
  }

  public function callbackChangeValue($element, $key, $target) {
    return 'Key: '.$key.', Value: '.$element;
  }
}
