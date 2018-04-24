<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorTreeChildrenTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorTreeChildren
  */
  public function testIterateRoot() {
    $iterator = $this->getIteratorFixture();
    $this->assertEquals(
      array(
        1 => 'one', 2 => 'two'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorTreeChildren
  */
  public function testIterateLeafs() {
    $iterator = new RecursiveIteratorIterator($this->getIteratorFixture());
    $this->assertEquals(
      array(
        3 => 'three', 2 => 'two'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorTreeChildren
  */
  public function testIterateAll() {
    $iterator = new RecursiveIteratorIterator(
      $this->getIteratorFixture(), RecursiveIteratorIterator::SELF_FIRST
    );
    $this->assertEquals(
      array(
        1 => 'one', 3 => 'three', 2 => 'two'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * A simple test data tree
  *
  * 1 => 'one'
  *   3 => 'tree'
  * 2 => 'two'
  *
  * The element id 4 is included int the children ids to simulate a missing element.
  *
  * @return PapayaIteratorTreeChildren
  */
  public function getIteratorFixture() {
    return new PapayaIteratorTreeChildren(
      array(
        1 => 'one',
        2 => 'two',
        3 => 'three'
      ),
      array(
        0 => array(1, 4, 2),
        1 => array(3)
      )
    );
  }
}
