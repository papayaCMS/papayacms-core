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

namespace Papaya\File\System {

  use Papaya\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\File\System\Directory
   */
  class DirectoryTest extends TestCase {

    public function tearDown() {
      $this->removeTemporaryDirectory();
    }

    public function testConstructor() {
      $directory = new Directory('/path/');
      $this->assertEquals(
        '/path', (string)$directory
      );
    }

    public function testExistsExpectingTrue() {
      $directory = new Directory(__DIR__);
      $this->assertTrue($directory->exists());
    }

    public function testExistsExpectingFalse() {
      $directory = new Directory(__DIR__.'NON_EXISTING');
      $this->assertFalse($directory->exists());
    }

    public function testIsReadableExpectingTrue() {
      $directory = new Directory(__DIR__);
      $this->assertTrue($directory->isReadable());
    }

    public function testIsWritableExpectingTrue() {
      $path = $this->createTemporaryDirectory();
      $directory = new Directory($path);
      $this->assertTrue($directory->isWritable());
    }

    public function testIsWriteableWithBCExpectingTrue() {
      $path = $this->createTemporaryDirectory();
      $directory = new Directory($path);
      /** @noinspection PhpDeprecationInspection */
      $this->assertTrue($directory->isWriteable());
    }

    public function testGetEntriesOnlyFiles() {
      $directory = new Directory(__DIR__.'/TestData/Directory');
      $this->assertEmpty(
        array_diff(
          [
            'sample-one.txt', 'sample-two.txt'
          ],
          array_keys(
            iterator_to_array($directory->getEntries('', Directory::FETCH_FILES))
          )
        )
      );
    }

    public function testGetEntriesWithFilter() {
      $directory = new Directory(__DIR__.'/TestData/Directory');
      $this->assertEquals(
        [
          'sample-one.txt'
        ],
        array_keys(
          iterator_to_array($directory->getEntries('(one)', Directory::FETCH_FILES))
        )
      );
    }

    public function testGetEntriesOnlyDirectories() {
      $directory = new Directory(__DIR__.'/TestData');
      $this->assertArrayHasKey(
        'Directory',
        iterator_to_array($directory->getEntries('', Directory::FETCH_DIRECTORIES))
      );
    }
  }
}
