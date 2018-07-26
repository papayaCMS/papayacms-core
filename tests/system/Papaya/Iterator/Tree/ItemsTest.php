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

class PapayaIteratorTreeItemsTest extends PapayaTestCase {

  /**
   * @covers \PapayaIteratorTreeItems
   */
  public function testIterationOnArray() {
    $iterator = new \PapayaIteratorTreeItems(
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
   * @covers \PapayaIteratorTreeItems
   */
  public function testIterationOnArrayValues() {
    $iterator = new \PapayaIteratorTreeItems(
      array('1', '2', '3'), \PapayaIteratorTreeItems::ATTACH_TO_VALUES
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
   * @covers \PapayaIteratorTreeItems
   */
  public function testIterationOnList() {
    $iterator = new \PapayaIteratorTreeItems(array('1', '2', '3'));
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
   * @covers \PapayaIteratorTreeItems
   */
  public function testIterationOnIterator() {
    $iterator = new \PapayaIteratorTreeItems(new ArrayIterator(array('1', '2', '3')));
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
   * @covers \PapayaIteratorTreeItems
   */
  public function testIterationOnItemHasRecursiveIterator() {
    $iterator = new \PapayaIteratorTreeItems(array('1', '2', '3'));
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
   * @covers \PapayaIteratorTreeItems
   */
  public function testDetachItemIterator() {
    $iterator = new \PapayaIteratorTreeItems(
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
