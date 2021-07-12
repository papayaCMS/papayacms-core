<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
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

  use Papaya\Filter\IntegerValue;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Iterator\Filter
   */
  class FilterTest extends TestCase {

    public function testFilterForIntegerValues() {
      $data = ['foo', 21, '42'];
      $iterator = new Filter(new \ArrayIterator($data), new IntegerValue());
      $this->assertSame(
        [1 => 21, 2 => 42],
        iterator_to_array($iterator)
      );
    }

    public function testFilterForIntegerKeys() {
      $data = ['foo' => 1, 21 => 2, '42' => 3];
      $iterator = new Filter(new \ArrayIterator($data), new IntegerValue(), Filter::FILTER_KEYS);
      $this->assertSame(
        [21 => 2, 42 => 3],
        iterator_to_array($iterator)
      );
    }

    public function testFilterForInteger() {
      $data = ['foo' => 1, 21 => 2, '42' => 3, 1 => 'foo'];
      $iterator = new Filter(new \ArrayIterator($data), new IntegerValue(), Filter::FILTER_BOTH);
      $this->assertSame(
        [21 => 2, 42 => 3],
        iterator_to_array($iterator)
      );
    }
  }
}
