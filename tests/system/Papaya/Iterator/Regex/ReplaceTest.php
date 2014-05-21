<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaIteratorRegexReplaceTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorRegexReplace
  */
  public function testIteration() {
    $iterator = new PapayaIteratorRegexReplace(
      new ArrayIterator(array('21 42', '42 84')),
      '(\d+)',
      '#$0'
    );
    $this->assertEquals(
      array(
        0 => '#21 #42',
        1 => '#42 #84'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorRegexReplace
  */
  public function testIterationLimitReplace() {
    $iterator = new PapayaIteratorRegexReplace(
      new ArrayIterator(array('21 42', '42 84')),
      '(\d+)',
      '#$0',
      1
    );
    $this->assertEquals(
      array(
        0 => '#21 42',
        1 => '#42 84'
      ),
      iterator_to_array($iterator)
    );
  }
}
