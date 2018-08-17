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

namespace Papaya\Media\File\Info;
require_once __DIR__.'/../../../../../bootstrap.php';

class SvgTest extends \Papaya\TestCase {

  public function testReadUsingXMLReader() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new Svg(__DIR__.'/TestData/minimum.svg');
    $this->assertTrue($info['is_valid']);
    $this->assertEquals(139, $info['width']);
    $this->assertEquals(144, $info['height']);
  }

  public function testReadInvalidUsingXMLReader() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new Svg('data://text/plain,');
    $this->assertFalse($info['is_valid']);
  }

  public function testReadUsingDOM() {
    $info = new Svg(__DIR__.'/TestData/minimum.svg');
    $info->forceDOM = TRUE;
    $this->assertTrue($info['is_valid']);
    $this->assertEquals(139, $info['width']);
    $this->assertEquals(144, $info['height']);
  }

  public function testReadInvalidUsingDOM() {
    if (!extension_loaded('xmlreader')) {
      $this->markTestSkipped('XMLReader not available');
    }
    $info = new Svg('data://text/plain,');
    $this->assertFalse($info['is_valid']);
  }

}
