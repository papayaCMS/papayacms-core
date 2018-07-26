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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFileSystemDirectoryTest extends PapayaTestCase {

  public function tearDown() {
    $this->removeTemporaryDirectory();
  }

  /**
   * @covers \PapayaFileSystemDirectory::__construct
   * @covers \PapayaFileSystemDirectory::__toString
   */
  public function testConstructor() {
    $directory = new \PapayaFileSystemDirectory('/path/');
    $this->assertEquals(
      '/path', (string)$directory
    );
  }

  /**
   * @covers \PapayaFileSystemDirectory::exists
   */
  public function testExistsExpectingTrue() {
    $directory = new \PapayaFileSystemDirectory(__DIR__);
    $this->assertTrue($directory->exists());
  }

  /**
   * @covers \PapayaFileSystemDirectory::exists
   */
  public function testExistsExpectingFalse() {
    $directory = new \PapayaFileSystemDirectory(__DIR__.'NON_EXISTING');
    $this->assertFalse($directory->exists());
  }

  /**
   * @covers \PapayaFileSystemDirectory::isReadable
   */
  public function testIsReadableExpectingTrue() {
    $directory = new \PapayaFileSystemDirectory(__DIR__);
    $this->assertTrue($directory->isReadable());
  }

  /**
   * @covers \PapayaFileSystemDirectory::isWriteable
   */
  public function testIsWriteableExpectingTrue() {
    $path = $this->createTemporaryDirectory();
    $directory = new \PapayaFileSystemDirectory($path);
    $this->assertTrue($directory->isWriteable());
  }

  /**
   * @covers \PapayaFileSystemDirectory::getEntries
   * @covers \PapayaFileSystemDirectory::callbackFileInfoIsFile
   */
  public function testGetEntriesOnlyFiles() {
    $directory = new \PapayaFileSystemDirectory(__DIR__.'/TestData/Directory');
    $this->assertEmpty(
      array_diff(
        array(
          'sample-one.txt', 'sample-two.txt'
        ),
        array_keys(
          iterator_to_array($directory->getEntries('', PapayaFileSystemDirectory::FETCH_FILES))
        )
      )
    );
  }

  /**
   * @covers \PapayaFileSystemDirectory::getEntries
   * @covers \PapayaFileSystemDirectory::callbackFileInfoIsFile
   */
  public function testGetEntriesWithFilter() {
    $directory = new \PapayaFileSystemDirectory(__DIR__.'/TestData/Directory');
    $this->assertEquals(
      array(
        'sample-one.txt'
      ),
      array_keys(
        iterator_to_array($directory->getEntries('(one)', PapayaFileSystemDirectory::FETCH_FILES))
      )
    );
  }

  /**
   * @covers \PapayaFileSystemDirectory::getEntries
   * @covers \PapayaFileSystemDirectory::callbackFileInfoIsDirectory
   */
  public function testGetEntriesOnlyDirectories() {
    $directory = new \PapayaFileSystemDirectory(__DIR__.'/TestData');
    $this->assertArrayHasKey(
      'Directory',
      iterator_to_array($directory->getEntries('', PapayaFileSystemDirectory::FETCH_DIRECTORIES))
    );
  }


}
