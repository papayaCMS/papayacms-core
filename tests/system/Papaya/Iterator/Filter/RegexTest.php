<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorFilterRegexTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorFilterRegex::__construct
  */
  public function testConstructor() {
    $filter = new PapayaIteratorFilterRegex(new ArrayIterator(array()), '(pattern)');
    $this->assertAttributeEquals(
      '(pattern)', '_pattern', $filter
    );
  }

  /**
  * @covers PapayaIteratorFilterRegex::__construct
  */
  public function testConstructorwithAllArguments() {
    $filter = new PapayaIteratorFilterRegex(
      new ArrayIterator(array()), '(pattern)', 42, PapayaIteratorFilterRegex::FILTER_BOTH
    );
    $this->assertAttributeEquals(
      42, '_offset', $filter
    );
    $this->assertAttributeEquals(
      PapayaIteratorFilterRegex::FILTER_BOTH, '_target', $filter
    );
  }

  /**
  * @covers PapayaIteratorFilterRegex::accept
  * @covers PapayaIteratorFilterRegex::isMatch
  */
  public function testAccept() {
    $data = array(
      'ok' => 'offset pattern',
      'fail string' => 'wrong',
      'fail offset' => 'pattern',
    );
    $filter = new PapayaIteratorFilterRegex(
      new ArrayIterator($data), '(pattern)', 4
    );
    $this->assertEquals(
      array('ok' => 'offset pattern'),
      iterator_to_array($filter, TRUE)
    );
  }

  /**
  * @covers PapayaIteratorFilterRegex::accept
  * @covers PapayaIteratorFilterRegex::isMatch
  */
  public function testAcceptUsingKeys() {
    $data = array(
      'ok' => 'offset pattern',
      'fail string' => 'wrong',
      'fail offset' => 'pattern',
    );
    $filter = new PapayaIteratorFilterRegex(
      new ArrayIterator(array_flip($data)), '(pattern)', 4, PapayaIteratorFilterRegex::FILTER_KEYS
    );
    $this->assertEquals(
      array('offset pattern' => 'ok'),
      iterator_to_array($filter, TRUE)
    );
  }

}
