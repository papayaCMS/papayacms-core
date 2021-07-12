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

namespace Papaya\Router {

  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Router\Path
   */
  class PathTest extends TestCase {

    public function testPathToArray() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two'];
      $this->assertSame(
        ['one', 'two'],
        iterator_to_array($path)
      );
    }

    public function testPathCount() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two'];
      $this->assertCount(2, $path);
    }

    /**
     * @param string $expected
     * @param int $level
     * @param int $offset
     * @testWith
     *   ["one/two/three", -1]
     *   ["one", 0]
     *   ["one/two", 1]
     *   ["one/two/three", 2]
     *   ["two/three", 2, 1]
     *   ["three", 2, 2]
     */
    public function testRoutePageToString($expected, $level, $offset = 0) {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two', 'three'];
      $this->assertSame(
        $expected,
        $path->getRouteString($level, $offset)
      );
    }

    public function testReadOffsetZero() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two', 'three'];
      $this->assertTrue(isset($path[0]));
      $this->assertSame('one', $path[0]);
    }

    public function testReadOffsetOne() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two', 'three'];
      $this->assertTrue(isset($path[1]));
      $this->assertSame('two', $path[1]);
    }

    public function testReadOffset42ExpectingFalse() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two', 'three'];
      $this->assertFalse(isset($path[42]));
      $this->assertNull($path[42]);
    }

    public function testWriteOffsetExpectingException() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two', 'three'];
      $this->expectException(\LogicException::class);
      $path[0] = 'path';
    }

    public function testUnsetOffsetExpectingException() {
      $path = new Path_TestProxy();
      $path->pathParts = ['one', 'two', 'three'];
      $this->expectException(\LogicException::class);
      unset($path[0]);
    }
  }

  class Path_TestProxy extends Path {

    public $pathParts = [];

    /**
     * Lazy parsing for the route path
     *
     * @param int $offset
     * @return array|null
     */
    public function getRouteArray($offset = 0) {
      return $this->pathParts;
    }

    /**
     * Lazy parsing for the route path
     *
     * @param $offset
     * @return string
     */
    public function getSeparator($offset) {
      return '/';
    }
  }

}
