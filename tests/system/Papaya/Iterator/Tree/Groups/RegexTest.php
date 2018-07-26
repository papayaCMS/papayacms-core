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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaIteratorTreeGroupsRegexTest extends PapayaTestCase {

  /**
   * @covers \PapayaIteratorTreeGroupsRegex
   */
  public function testIteration() {
    $iterator = new \PapayaIteratorTreeGroupsRegex(
      array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
        /** @lang Text */'(^(?P<char>.))',
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
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \PapayaIteratorTreeGroupsRegex
   */
  public function testIterationKeepIndex() {
    $iterator = new \PapayaIteratorTreeGroupsRegex(
      array('admin' => 'Administration', 'app' => 'Application'),
      /** @lang Text */'(^(?P<char>.))',
      'char'
    );
    $this->assertEquals(
      array(
         0 => 'A',
         'admin' => 'Administration',
         'app' => 'Application'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }

  /**
   * @covers \PapayaIteratorTreeGroupsRegex
   */
  public function testIterationWithoutMatchExpectingElementAsGroup() {
    $iterator = new \PapayaIteratorTreeGroupsRegex(
      array('Administration', 'Application'),
      '(^-$)'
    );
    $this->assertEquals(
      array(
         0 => 'Administration',
         1 => 'Application'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \PapayaIteratorTreeGroupsRegex
   */
  public function testIterationWithoutMatchKeepingEmptyStringIndex() {
    $iterator = new \PapayaIteratorTreeGroupsRegex(
      array('' => 'Empty', 'some' => 'Filled'),
      '(^-$)'
    );
    $this->assertEquals(
      array(
         '' => 'Empty',
         'some' => 'Filled'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        TRUE
      )
    );
  }

  /**
   * @covers \PapayaIteratorTreeGroupsRegex
   */
  public function testIterationWithNotAllItemsMatchingToAGroup() {
    $iterator = new \PapayaIteratorTreeGroupsRegex(
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
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST),
        FALSE
      )
    );
  }

  /**
   * @covers \PapayaIteratorTreeGroupsRegex
   */
  public function testIterationMatchingKeys() {
    $iterator = new \PapayaIteratorTreeGroupsRegex(
      array('admin' => 'Administration', 'app' => 'Application'),
      /** @lang Text */'(^(?P<char>.))',
      'char',
      \PapayaIteratorTreeGroupsRegex::GROUP_KEYS
    );
    $this->assertEquals(
      array(
         0 => 'a',
         'admin' => 'Administration',
         'app' => 'Application'
      ),
      iterator_to_array(
        new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST)
      )
    );
  }
}
