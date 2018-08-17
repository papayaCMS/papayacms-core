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

namespace Papaya\Iterator;
require_once __DIR__.'/../../../bootstrap.php';

class ArrayMapperTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Iterator\ArrayMapper
   */
  public function testIteration() {
    $iterator = new ArrayMapper(
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
   * @covers \Papaya\Iterator\ArrayMapper
   */
  public function testIterationWithMultipleNames() {
    $iterator = new ArrayMapper(
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
   * @covers \Papaya\Iterator\ArrayMapper
   */
  public function testIterationWithNonExistingNames() {
    $iterator = new ArrayMapper(
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
