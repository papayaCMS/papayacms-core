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

namespace Papaya\Graphics {
  require_once __DIR__.'/../../../bootstrap.php';

  class BoundingRectangleTest extends \Papaya\TestCase {

    public function testGetOffset() {
      $bounds = new BoundingRectangle(1,2,3,4);
      $this->assertEquals([1,2], $bounds->getOffset());
    }

    public function testGetSize() {
      $bounds = new BoundingRectangle(1,2,3,4);
      $this->assertEquals([3,4], $bounds->getOffset());
    }

    public function testGetLeft() {
      $bounds = new BoundingRectangle(42,21,30,40);
      $this->assertEquals(42, $bounds->getLeft());
    }

    public function testGetTop() {
      $bounds = new BoundingRectangle(42,21,30,40);
      $this->assertEquals(21, $bounds->getTop());
    }

    public function testGetWidth() {
      $bounds = new BoundingRectangle(42,21,30,40);
      $this->assertEquals(30, $bounds->getWidth());
    }

    public function testGetHeight() {
      $bounds = new BoundingRectangle(42,21,30,40);
      $this->assertEquals(40, $bounds->getHeight());
    }


  }
}
