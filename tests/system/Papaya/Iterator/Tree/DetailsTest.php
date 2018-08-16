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

namespace Papaya\Iterator\Tree;
require_once __DIR__.'/../../../../bootstrap.php';

class DetailsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Iterator\Tree\Details
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
    $iterator = new Details($main, $details, 'category_id');
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
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Details
   */
  public function testIterationWithIterators() {
    $main = new \ArrayIterator(
      array(
        1 => array('title' => 'CategoryOne'),
        2 => array('title' => 'CategoryTwo'),
        3 => array('title' => 'CategoryThree')
      )
    );
    $details = new \ArrayIterator(
      array(
        1 => array('title' => '1.1', 'category_id' => 1),
        2 => array('title' => '1.2', 'category_id' => 1),
        3 => array('title' => '2.1', 'category_id' => 2)
      )
    );
    $iterator = new Details($main, $details, 'category_id');
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
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Details
   */
  public function testIterationGroupedByKey() {
    $main = new \ArrayIterator(
      array(
        1 => 'CategoryOne',
        2 => 'CategoryTwo',
        3 => 'CategoryThree'
      )
    );
    $details = new \ArrayIterator(
      array(
        1 => array('1.1', '1.2'),
        2 => array('2.1')
      )
    );
    $iterator = new Details($main, $details);
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
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }
}
