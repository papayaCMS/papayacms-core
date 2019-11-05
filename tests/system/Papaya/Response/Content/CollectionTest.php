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

namespace Papaya\Response\Content {

  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Response\Content\Collection
   */
  class CollectionTest extends TestCase {

    public function testLength() {
      $content = new Collection(new \EmptyIterator());
      $this->assertSame(-1, $content->length());
    }

    /**
     * @param string $expected
     * @param array $data
     * @param string $linebreak
     * @testWith
     *   ["foo\nbar\n", ["foo", "bar"]]
     *   ["foo|bar|", ["foo", "bar"], "|"]
     */
    public function testOutput($expected, $data, $linebreak = "\n") {
      $content = new Collection(new \ArrayIterator($data), $linebreak);
      $this->expectOutputString($expected);
      $content->output();
    }
  }

}
