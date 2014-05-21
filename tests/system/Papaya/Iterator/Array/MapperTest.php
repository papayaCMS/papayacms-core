<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaIteratorArrayMapperTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorArrayMapper
  */
  public function testIteration() {
    $iterator = new PapayaIteratorArrayMapper(
      array(
        1 => array('title' => 'foo'),
        2 => array('title' => 'bar')
      ),
      'title'
    );
    $this->assertEquals(
      array(
        1 => 'foo',
        2 => 'bar'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorArrayMapper
  */
  public function testIterationWithMultipleNames() {
    $iterator = new PapayaIteratorArrayMapper(
      array(
        1 => array('title' => 'foo'),
        2 => array('caption' => 'bar')
      ),
      array('caption', 'title')
    );
    $this->assertEquals(
      array(
        1 => 'foo',
        2 => 'bar'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaIteratorArrayMapper
  */
  public function testIterationWithNonExistingNames() {
    $iterator = new PapayaIteratorArrayMapper(
      array(
        1 => array('title' => 'foo'),
        2 => array('caption' => 'bar')
      ),
      23
    );
    $this->assertEquals(
      array(
        1 => NULL,
        2 => NULL
      ),
      iterator_to_array($iterator)
    );
  }

}