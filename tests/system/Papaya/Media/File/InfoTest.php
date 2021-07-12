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

namespace Papaya\Media\File {

  use BadMethodCallException;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Media\File\Info
   */
  class InfoTest extends TestCase {

    public function testConstructor() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt');
      $this->assertSame(__DIR__.'/TestData/example.txt', $info->getFile());
    }

    public function testConstructorWithOriginalFileName() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt', 'original.txt');
      $this->assertSame(__DIR__.'/TestData/example.txt', $info->getFile());
      $this->assertSame('original.txt', $info->getOriginalFileName());
    }

    public function testIsSupportedReturnsTrue() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt');
      $this->assertTrue($info->isSupported());
    }

    public function testGetIterator() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt', 'original.txt');
      $this->assertSame(['filesize' => 8], iterator_to_array($info));
    }

    public function testOffsetGet() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt', 'original.txt');
      $this->assertTrue(isset($info['filesize']));
      $this->assertSame(8, $info['filesize']);
    }

    public function testOffsetSetExpectingException() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt', 'original.txt');
      $this->expectException(BadMethodCallException::class);
      $this->expectExceptionMessage('Object Papaya\Media\File\Info_TestProxy is immutable.');
      $info['filesize'] = 9;
    }

    public function testOffsetUnsetExpectingException() {
      $info = new Info_TestProxy(__DIR__.'/TestData/example.txt', 'original.txt');
      $this->expectException(BadMethodCallException::class);
      $this->expectExceptionMessage('Object Papaya\Media\File\Info_TestProxy is immutable.');
      unset($info['filesize']);
    }
  }

  class Info_TestProxy extends Info {

  }
}
