<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaIteratorTreeDetailsTest extends PapayaTestCase {

  /**
   * @covers PapayaIteratorTreeDetails
   */
  public function testIterationWithArray() {
    $main = array(
      1 => array('title' => 'CategoryOne'),
      2 => array('title' => 'CategoryTwo'),
      3 => array('title' => 'CategoryThree')
    );
    $details = array(
      1 => array('title' => '1.1', 'category_id' => 1),
      2 => array('title' => '1.2', 'category_id' => 1),
      3 => array('title' => '2.1', 'category_id' => 2)
    );
    $iterator = new PapayaIteratorTreeDetails($main, $details, 'category_id');
    $this->assertEquals(
      array(
        array(
          'title' => 'CategoryOne'
        ),
        array(
          'title' => '1.1',
          'category_id' => 1
        ),
        array(
          'title' => '1.2',
          'category_id' => 1
        ),
        array(
          'title' => 'CategoryTwo'
        ),
        array(
          'title' => '2.1',
          'category_id' => 2
        ),
        array(
          'title' => 'CategoryThree'
        ),
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeDetails
   */
  public function testIterationWithIterators() {
    $main = new ArrayIterator(
      array(
        1 => array('title' => 'CategoryOne'),
        2 => array('title' => 'CategoryTwo'),
        3 => array('title' => 'CategoryThree')
      )
    );
    $details = new ArrayIterator(
      array(
        1 => array('title' => '1.1', 'category_id' => 1),
        2 => array('title' => '1.2', 'category_id' => 1),
        3 => array('title' => '2.1', 'category_id' => 2)
      )
    );
    $iterator = new PapayaIteratorTreeDetails($main, $details, 'category_id');
    $this->assertEquals(
      array(
        array(
          'title' => 'CategoryOne'
        ),
        array(
          'title' => '1.1',
          'category_id' => 1
        ),
        array(
          'title' => '1.2',
          'category_id' => 1
        ),
        array(
          'title' => 'CategoryTwo'
        ),
        array(
          'title' => '2.1',
          'category_id' => 2
        ),
        array(
          'title' => 'CategoryThree'
        ),
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers PapayaIteratorTreeDetails
   */
  public function testIterationGroupedByKey() {
    $main = new ArrayIterator(
      array(
        1 => 'CategoryOne',
        2 => 'CategoryTwo',
        3 => 'CategoryThree'
      )
    );
    $details = new ArrayIterator(
      array(
        1 => array('1.1', '1.2'),
        2 => array('2.1')
      )
    );
    $iterator = new PapayaIteratorTreeDetails($main, $details);
    $this->assertEquals(
      array(
        0 => 'CategoryOne',
        1 => array(
          0 => '1.1',
          1 => '1.2'
        ),
        2 => 'CategoryTwo',
        3 => array(
          0 => '2.1',
        ),
        4 => 'CategoryThree'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }
}