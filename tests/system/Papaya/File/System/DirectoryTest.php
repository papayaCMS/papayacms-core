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

namespace Papaya\File\System;

require_once __DIR__.'/../../../../bootstrap.php';

class DirectoryTest extends \PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
   * @covers \Papaya\File\System\Directory::__construct
   * @covers \Papaya\File\System\Directory::__toString
   */
  public function testConstructor() {
    $directory = new Directory('/path/');
    $this->assertEquals(
      '/path', (string)$directory
    );
  }

  /**
   * @covers \Papaya\File\System\Directory::exists
   */
  public function testExistsExpectingTrue() {
    $directory = new Directory(__DIR__);
    $this->assertTrue($directory->exists());
  }

  /**
   * @covers \Papaya\File\System\Directory::exists
   */
  public function testExistsExpectingFalse() {
    $directory = new Directory(__DIR__.'NON_EXISTING');
    $this->assertFalse($directory->exists());
  }

  /**
   * @covers \Papaya\File\System\Directory::isReadable
   */
  public function testIsReadableExpectingTrue() {
    $directory = new Directory(__DIR__);
    $this->assertTrue($directory->isReadable());
  }

  /**
   * @covers \Papaya\File\System\Directory::isWriteable
   */
  public function testIsWriteableExpectingTrue() {
    $path = $this->createTemporaryDirectory();
    $directory = new Directory($path);
    $this->assertTrue($directory->isWriteable());
  }

  /**
   * @covers \Papaya\File\System\Directory::getEntries
   * @covers \Papaya\File\System\Directory::callbackFileInfoIsFile
   */
  public function testGetEntriesOnlyFiles() {
    $directory = new Directory(__DIR__.'/TestData/Directory');
    $this->assertEmpty(
      array_diff(
        array(
          'sample-one.txt', 'sample-two.txt'
        ),
        array_keys(
          iterator_to_array($directory->getEntries('', Directory::FETCH_FILES))
        )
      )
    );
  }

  /**
   * @covers \Papaya\File\System\Directory::getEntries
   * @covers \Papaya\File\System\Directory::callbackFileInfoIsFile
   */
  public function testGetEntriesWithFilter() {
    $directory = new Directory(__DIR__.'/TestData/Directory');
    $this->assertEquals(
      array(
        'sample-one.txt'
      ),
      array_keys(
        iterator_to_array($directory->getEntries('(one)', Directory::FETCH_FILES))
      )
    );
  }

  /**
   * @covers \Papaya\File\System\Directory::getEntries
   * @covers \Papaya\File\System\Directory::callbackFileInfoIsDirectory
   */
  public function testGetEntriesOnlyDirectories() {
    $directory = new Directory(__DIR__.'/TestData');
    $this->assertArrayHasKey(
      'Directory',
      iterator_to_array($directory->getEntries('', Directory::FETCH_DIRECTORIES))
    );
  }


}
