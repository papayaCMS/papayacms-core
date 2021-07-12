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

namespace Papaya\Media\File {

  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Media\File\Properties
   */
  class PropertiesTest extends TestCase {

    public function testFetchPropertiesFromInfoImplementation() {
      $infoMock = $this->createMock(\Papaya\Media\File\Info::class);
      $infoMock
        ->expects($this->once())
        ->method('isSupported')
        ->willReturn(TRUE);
      $infoMock
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn(new \ArrayIterator(['foo' => 'bar']));
      $info = new Properties(__FILE__);
      $info->fetchers($infoMock);

      $this->assertEquals(
        ['foo' => 'bar'],
        iterator_to_array($info)
      );
    }

    public function testFetchPropertiesForInvalidFile() {
      $info = new Properties(__DIR__.'/non-existing.file');
      $this->assertEquals(
        ['is_valid' => FALSE],
        iterator_to_array($info)
      );
    }

    public function testLazyInitializationOfFetchers() {
      $info = new Properties('example.file');
      $this->assertCount(4, $info->fetchers());
    }
  }
}
