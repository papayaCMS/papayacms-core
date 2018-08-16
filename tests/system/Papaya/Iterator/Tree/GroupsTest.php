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

namespace Papaya\Iterator\Tree {

  require_once __DIR__.'/../../../../bootstrap.php';

  class GroupsTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\Iterator\Tree\Groups
     */
    public function testIterationWithStringsGroupByFirstChar() {
      $iterator = new Groups(
        array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
        array($this, 'callbackGetFirstChar')
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
     * @covers \Papaya\Iterator\Tree\Groups
     */
    public function testIterationWithStringsGeneratingGroupArrays() {
      $iterator = new Groups(
        array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
        array($this, 'callbackGetFirstCharAsArray')
      );
      $this->assertEquals(
        array(
          0 => array('character' => 'A'),
          1 => 'Administration',
          2 => 'Application',
          3 => array('character' => 'C'),
          4 => 'Cache',
          5 => 'Configuration',
          6 => array('character' => 'I'),
          7 => 'Iterator'
        ),
        iterator_to_array(
          new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
          FALSE
        )
      );
    }

    /**
     * @covers \Papaya\Iterator\Tree\Groups
     */
    public function testIterationWithStringsGeneratingGroupObjects() {
      $iterator = new Groups(
        array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
        array($this, 'callbackGetFirstCharAsObject')
      );
      $this->assertEquals(
        array(
          0 => new SampleGroup('A'),
          1 => 'Administration',
          2 => 'Application',
          3 => new SampleGroup('C'),
          4 => 'Cache',
          5 => 'Configuration',
          6 => new SampleGroup('I'),
          7 => 'Iterator'
        ),
        iterator_to_array(
          new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
          FALSE
        )
      );
    }

    /**
     * @covers \Papaya\Iterator\Tree\Groups
     */
    public function testIterationWithStringsWithInvalidGroupAddingItemsToFirstLevel() {
      $iterator = new Groups(
        array('Administration', 'Application', 'Cache', 'Configuration', 'Iterator'),
        array($this, 'callbackGetNull')
      );
      $this->assertEquals(
        array(
          0 => 'Administration',
          1 => 'Application',
          2 => 'Cache',
          3 => 'Configuration',
          4 => 'Iterator'
        ),
        iterator_to_array(
          new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST),
          FALSE
        )
      );
    }

    /**
     * @covers \Papaya\Iterator\Tree\Groups
     */
    public function testIterationKeepsKeys() {
      $iterator = new Groups(
        array(
          'admin' => 'Administration',
          'app' => 'Application',
          '' => 'Cache'
        ),
        array($this, 'callbackGetFirstCharIfA')
      );
      $this->assertEquals(
        array(
          0 => 'A',
          'admin' => 'Administration',
          'app' => 'Application',
          '' => 'Cache'
        ),
        iterator_to_array(
          new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST)
        )
      );
    }

    public function callbackGetFirstChar($element) {
      return $element[0];
    }

    public function callbackGetFirstCharAsArray($element) {
      return array('character' => $element[0]);
    }

    public function callbackGetFirstCharAsObject($element) {
      return new SampleGroup($element[0]);
    }

    public function callbackGetNull() {
      return NULL;
    }

    public function callbackGetFirstCharIfA($element) {
      return (0 === strpos($element, 'A')) ? 'A' : NULL;
    }
  }

  class SampleGroup {
    public $character = '';

    public function __construct($character) {
      $this->character = $character;
    }
  }
}
