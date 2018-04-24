<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorRepeatCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorRepeatCallback
  */
  public function testIteration() {
    $iterator = new PapayaIteratorRepeatCallback(array($this, 'incrementToThree'), 0);
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorRepeatCallback
  */
  public function testIterationAfterRewind() {
    $iterator = new PapayaIteratorRepeatCallback(array($this, 'incrementToThree'), 0);
    $first = iterator_to_array($iterator);
    $this->assertEquals(
      array(1, 2, 3),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorRepeatCallback
  */
  public function testConstructorWithInvalidCallbackExpectingException() {
    $this->setExpectedException(
      'InvalidArgumentException', 'Invalid callback provided.'
    );
    $iterator = new PapayaIteratorRepeatCallback(NULL);
  }

  public function incrementToThree($value, $key) {
    $value++;
    $key++;
    if ($value < 4) {
      return array($value, $key);
    } else {
      return FALSE;
    }
  }
}
