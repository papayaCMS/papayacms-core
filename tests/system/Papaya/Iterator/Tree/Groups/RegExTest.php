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

namespace Papaya\Iterator\Tree\Groups;
require_once __DIR__.'/../../../../../bootstrap.php';

class RegExTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Iterator\Tree\Groups\RegEx
   */
  public function testIteration() {
    $iterator = new RegEx(
      array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
      /** @lang Text */
      '(^(?P<char>.))',
      'char'
    );
    $this->assertEquals(
      array(
        0 => 'A',
        1 => 'Administration',
        2 => 'Application',
        3 => 'C',
        4 => 'Cache',
        5 => 'Configuration',
        6 => 'I',
        7 => 'Iterator'
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Groups\RegEx
   */
  public function testIterationKeepIndex() {
    $iterator = new RegEx(
      array('admin' => 'Administration', 'app' => 'Application'),
      /** @lang Text */
      '(^(?P<char>.))',
      'char'
    );
    $this->assertEquals(
      array(
        0 => 'A',
        'admin' => 'Administration',
        'app' => 'Application'
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Groups\RegEx
   */
  public function testIterationWithoutMatchExpectingElementAsGroup() {
    $iterator = new RegEx(
      array('Administration', 'Application'),
      '(^-$)'
    );
    $this->assertEquals(
      array(
        0 => 'Administration',
        1 => 'Application'
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Groups\RegEx
   */
  public function testIterationWithoutMatchKeepingEmptyStringIndex() {
    $iterator = new RegEx(
      array('' => 'Empty', 'some' => 'Filled'),
      '(^-$)'
    );
    $this->assertEquals(
      array(
        '' => 'Empty',
        'some' => 'Filled'
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        TRUE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Groups\RegEx
   */
  public function testIterationWithNotAllItemsMatchingToAGroup() {
    $iterator = new RegEx(
      array('Administration', '# no group', 'Application'),
      '(^\w)'
    );
    $this->assertEquals(
      array(
        0 => 'A',
        1 => 'Administration',
        2 => 'Application',
        3 => '# no group'
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \Papaya\Iterator\Tree\Groups\RegEx
   */
  public function testIterationMatchingKeys() {
    $iterator = new RegEx(
      array('admin' => 'Administration', 'app' => 'Application'),
      /** @lang Text */
      '(^(?P<char>.))',
      'char',
      RegEx::GROUP_KEYS
    );
    $this->assertEquals(
      array(
        0 => 'a',
        'admin' => 'Administration',
        'app' => 'Application'
      ),
      iterator_to_array(
        new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}
