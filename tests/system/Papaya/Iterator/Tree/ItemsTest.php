<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorTreeItemsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Iterator\Tree\Items
   */
  public function testIterationOnArray() {
    $iterator = new \Papaya\Iterator\Tree\Items(
      array('one' => '1', 'two' => '2', 'tree' => '3')
    );
    $iterator->attachItemIterator('two', array('two_one' => '2.1'));

    $this->assertEquals(
      array('one' => '1', 'two' => '2', 'two_one' => '2.1', 'tree' => '3'),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
  /**
   * @covers \Papaya\Iterator\Tree\Items
   */
  public function testIterationOnArrayValues() {
    $iterator = new \Papaya\Iterator\Tree\Items(
      array('1', '2', '3'), \Papaya\Iterator\Tree\Items::ATTACH_TO_VALUES
    );
    $iterator->attachItemIterator(2, array(2 => '2.1'));

    $this->assertEquals(
      array('1', '2', '2.1', '3'),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Items
   */
  public function testIterationOnList() {
    $iterator = new \Papaya\Iterator\Tree\Items(array('1', '2', '3'));
    $iterator->attachItemIterator(1, array('2.1', '2.2'));
    $this->assertEquals(
      array('1', '2', '2.1', '2.2', '3'),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Items
   */
  public function testIterationOnIterator() {
    $iterator = new \Papaya\Iterator\Tree\Items(new ArrayIterator(array('1', '2', '3')));
    $iterator->attachItemIterator(1, new ArrayIterator(array('2.1', '2.2')));
    $this->assertEquals(
      array('1', '2', '2.1', '2.2', '3'),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Items
   */
  public function testIterationOnItemHasRecursiveIterator() {
    $iterator = new \Papaya\Iterator\Tree\Items(array('1', '2', '3'));
    $iterator->attachItemIterator(
      1, new RecursiveArrayIterator(array('2.1', array('2.1.1', '2.1.2'), '2.2'))
    );
    $this->assertEquals(
      array('1', '2', '2.1', array('2.1.1', '2.1.2'), '2.1.1', '2.1.2', '2.2', '3'),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Items
   */
  public function testDetachItemIterator() {
    $iterator = new \Papaya\Iterator\Tree\Items(
      array('one' => '1', 'two' => '2', 'tree' => '3')
    );
    $iterator->attachItemIterator('two', array('two_one' => '2.1'));
    $iterator->detachItemIterator('two');

    $this->assertEquals(
      array('one' => '1', 'two' => '2', 'tree' => '3'),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}
