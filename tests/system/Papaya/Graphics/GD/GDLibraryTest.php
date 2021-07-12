<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Graphics\GD {
  require_once __DIR__.'/../../../../bootstrap.php';

  class GDLibraryTest extends \Papaya\TestFramework\TestCase {

    public function testLoad() {
      $gd = new GDLibrary();
      $image = $gd->load(__DIR__.'/TestData/square.png');
      $this->assertInstanceOf(GDImage::class, $image);
      $this->assertEquals(10, $image->getWidth());
      $this->assertEquals(10, $image->getHeight());
    }

    public function testCreate() {
      $gd = new GDLibrary();
      $image = $gd->create(21,42);
      $this->assertInstanceOf(GDImage::class, $image);
      $this->assertEquals(21, $image->getWidth());
      $this->assertEquals(42, $image->getHeight());
    }
  }
}
