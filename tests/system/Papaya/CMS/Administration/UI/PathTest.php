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

namespace Papaya\CMS\Administration\UI {

  use Papaya\TestFramework\TestCase;
  use Papaya\URL;

  /**
   * @covers \Papaya\CMS\Administration\UI\Path
   */
  class PathTest extends TestCase {

    /**
     * @param array $expected
     * @param string $urlPath
     * @param int $offset
     * @testWith
     *   [[""], ""]
     *   [["foo"], "foo"]
     *   [["foo", "bar"], "foo.bar"]
     *   [["foo", "bar"], "foo/bar"]
     *   [["foo", "bar", "42"], "foo.bar/42"]
     *   [["bar"], "foo.bar", 1]
     *   [["bar", "42"], "foo.bar/42", 1]
     *   [["42"], "foo.bar/42", 2]
     */
    public function testGetRouteArray($expected, $urlPath, $offset = 0) {
      $path = new Path('base', new URL('http://example.tld/base/'.$urlPath));
      $this->assertSame(
        $expected, $path->getRouteArray($offset)
      );
    }

    public function testGetRouteArrayForDifferentPath() {
      $path = new Path('base', new URL('http://example.tld/other/'));
      $this->assertSame(
        [], $path->getRouteArray()
      );
    }

    /**
     * @param array $expected
     * @param string $urlPath
     * @param int $offset
     * @testWith
     *   [".", ""]
     *   [".", "foo"]
     *   [".", "foo.bar"]
     *   ["/", "foo/bar"]
     *   [".", "foo.bar/42"]
     *   [".", "foo.bar", 1]
     *   ["/", "foo.bar/42", 1]
     *   [".", "foo.bar/42", 2]
     */
    public function testGetSeparator($expected, $urlPath, $offset = 0) {
      $path = new Path('base', new URL('http://example.tld/base/'.$urlPath));
      $this->assertSame(
        $expected, $path->getSeparator($offset)
      );
    }

    public function testGetSeparatorForDifferentPath() {
      $path = new Path('base', new URL('http://example.tld/other/'));
      $this->assertSame(
        '.', $path->getSeparator(0)
      );
    }
  }

}
