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

namespace Papaya\Iterator {

  /**
   * @covers \Papaya\Iterator\SortIterator
   */
  class SortIteratorTest extends \Papaya\TestCase {

    public function testConstructor() {
      $traversable = new \ArrayIterator([]);
      $iterator = new SortIterator($traversable);
      $this->assertSame(
        $traversable, $iterator->getInnerIterator()
      );
    }

    public function testBasicSortingWithoutKeys() {
      $iterator = new SortIterator(['charlie', 'alpha', 'beta'], NULL, SortIterator::IGNORE_KEYS);
      $this->assertSame(
        ['alpha', 'beta', 'charlie'], iterator_to_array($iterator)
      );
    }

    public function testBasicSortingWithKeys() {
      $iterator = new SortIterator(
        ['1' => 'charlie', '2' => 'alpha', '3' => 'beta']
      );
      $this->assertSame(
        ['2' => 'alpha', '3' => 'beta', '1' => 'charlie'], iterator_to_array($iterator)
      );
    }

    public function testSortingWithCompareMethod() {
      $iterator = new SortIterator(
        ['1' => 'charlie', '2' => 'alpha', '3' => 'beta'],
        static function($a, $b) {
          return strnatcasecmp($a, $b) * -1;
        }
      );
      $this->assertSame(
        ['1' => 'charlie', '3' => 'beta', '2' => 'alpha'], iterator_to_array($iterator)
      );
    }

    public function testSortingKeysWithCompareMethod() {
      $iterator = new SortIterator(
        ['charlie' => '1', 'alpha' => '2', 'beta' => '3'],
        static function($a, $b) {
          return strnatcasecmp($a, $b) * -1;
        },
        SortIterator::SORT_KEYS
      );
      $this->assertSame(
        ['charlie' => '1', 'beta' => '3', 'alpha' => '2'], iterator_to_array($iterator)
      );
    }
  }
}
