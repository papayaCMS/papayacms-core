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

namespace Papaya\Media\File\Info {
  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\Media\File\Info\SVG
   */
  class SVGTest extends \Papaya\TestFramework\TestCase {

    public function testIsSupportedExtensionExpectingTrue() {
      $info = new SVG(__DIR__.'/TestData/minimum.svg', 'minimum.svg');
      $this->assertTrue($info->isSupported([]));
    }

    public function testIsSupportedMimeTypeExpectingTrue() {
      $info = new SVG(__DIR__.'/TestData/minimum.ext');
      $this->assertTrue($info->isSupported(['mimetype' => 'image/svg+xml']));
    }

    public function testReadUsingXMLReader() {
      if (!extension_loaded('xmlreader')) {
        $this->markTestSkipped('XMLReader not available');
      }
      $info = new SVG(__DIR__.'/TestData/minimum.svg');
      $this->assertTrue($info['is_valid']);
      $this->assertEquals(139, $info['width']);
      $this->assertEquals(144, $info['height']);
    }

    public function testReadInvalidUsingXMLReader() {
      if (!extension_loaded('xmlreader')) {
        $this->markTestSkipped('XMLReader not available');
      }
      $info = new SVG('data://text/plain,');
      $this->assertFalse($info['is_valid']);
    }

    public function testReadUsingDOM() {
      $info = new SVG(__DIR__.'/TestData/minimum.svg');
      $info->forceDOM = TRUE;
      $this->assertTrue($info['is_valid']);
      $this->assertEquals(139, $info['width']);
      $this->assertEquals(144, $info['height']);
    }

    public function testReadInvalidUsingDOM() {
      if (!extension_loaded('xmlreader')) {
        $this->markTestSkipped('XMLReader not available');
      }
      $info = new SVG('data://text/plain,');
      $this->assertFalse($info['is_valid']);
    }

  }

}
