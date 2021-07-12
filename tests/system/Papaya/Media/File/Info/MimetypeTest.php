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

namespace Papaya\Media\File\Info {

  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Media\File\Info\Mimetype
   */
  class MimetypeTest extends TestCase {

    public function testWithInvalidFile() {
      $info = new Mimetype(__DIR__.'/non-existing.file');
      $this->assertSame('application/octet-stream', $info['mimetype']);
    }
    public function testWithValidPHPFile() {
      $info = new Mimetype(__FILE__);
      $this->assertSame('text/x-php', $info['mimetype']);
    }

    public function testWithValidPNGFile() {
      $info = new Mimetype(__DIR__.'/TestData/20x20.png');
      $this->assertSame('image/png', $info['mimetype']);
    }

    public function testWithValidSVGFile() {
      $info = new Mimetype(__DIR__.'/TestData/minimum.svg');
      $this->assertSame('image/svg+xml', $info['mimetype']);
    }
  }

}
