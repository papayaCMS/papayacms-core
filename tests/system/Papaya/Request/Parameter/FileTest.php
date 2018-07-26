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

class PapayaRequestParameterFileTest extends \PapayaTestCase {

  /**
   * @covers \PapayaRequestParameterFile::__construct
   * @covers \PapayaRequestParameterFile::getName
   */
  public function testConstructor() {
    $file = new \PapayaRequestParameterFile('foo');
    $this->assertEquals(array('foo'), iterator_to_array($file->getName()));
  }

  /**
   * @covers \PapayaRequestParameterFile::__construct
   * @covers \PapayaRequestParameterFile::getName
   */
  public function testConstructorWithNameAndGroup() {
    $file = new \PapayaRequestParameterFile('foo/bar', 'group');
    $this->assertEquals(array('group', 'foo', 'bar'), iterator_to_array($file->getName()));
  }

  /**
   * @covers \PapayaRequestParameterFile::__construct
   * @covers \PapayaRequestParameterFile::getName
   */
  public function testConstructorWithNameObject() {
    $name = $this
      ->getMockBuilder(\PapayaRequestParametersName::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file = new \PapayaRequestParameterFile($name);
    $this->assertSame($name, $file->getName());
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testToString() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals('/tmp/file', (string)$file);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testToStringWithoutData() {
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals('', (string)$file);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testToStringWithInvalidFile() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(FALSE));
    $this->assertEquals('', (string)$file);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testIsValidExpectingTrue() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertTrue($file->isValid());
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testisValidExpectingFalse() {
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertFalse($file->isValid());
  }

  /**
   * @covers \PapayaRequestParameterFile::getIterator
   */
  public function testGetIterator() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals(
      array(
        'temporary' => '/tmp/file',
        'name' => 'file.ext',
        'size' => 42,
        'type' => 'some/sample',
        'error' => 0
      ),
      iterator_to_array($file)
    );
  }

  /**
   * @covers \PapayaRequestParameterFile
   */
  public function testOffsetExists() {
    $file = new \PapayaRequestParameterFile('foo');
    $this->assertTrue(isset($file['name']));
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testOffsetExistsForTemporaryFile() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertTrue(isset($file['temporary']));
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testOffsetGetForTemporaryFile() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals('/tmp/file', $file['temporary']);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testOffsetGetForTemporaryFileWithInvalidFile() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(FALSE));
    $this->assertNull($file['temporary']);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testOffsetGetForName() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals('file.ext', $file['name']);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testOffsetGetForSize() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals(42, $file['size']);
  }

  /**
   * @covers \PapayaRequestParameterFile
   * @backupGlobals enabled
   */
  public function testOffsetGetForType() {
    $_FILES = $this->getFileParametersFixture();
    $file = new \PapayaRequestParameterFile('foo');
    $file->fileSystem($this->getFileSystemFixtureWithUploadedFile(TRUE));
    $this->assertEquals('some/sample', $file['type']);
  }

  /**
   * @covers \PapayaRequestParameterFile
   */
  public function testOffsetSetExpectingException() {
    $file = new \PapayaRequestParameterFile('foo');
    $this->expectException(LogicException::class);
    $file['type'] = '';
  }

  /**
   * @covers \PapayaRequestParameterFile
   */
  public function testOffsetUnsetExpectingException() {
    $file = new \PapayaRequestParameterFile('foo');
    $this->expectException(LogicException::class);
    unset($file['size']);
  }

  /*************************************
   * Fixtures
   *************************************/

  public function getFileParametersFixture() {
    return array(
      'foo' => array(
        'tmp_name' => '/tmp/file',
        'name' => 'file.ext',
        'size' => 42,
        'type' => 'some/sample',
        'error' => 0
      )
    );
  }

  public function getFileSystemFixtureWithUploadedFile($isUploadedFile) {
    $file = $this
      ->getMockBuilder(\Papaya\File\System\File::class)
      ->disableOriginalConstructor()
      ->getMock();
    $file
      ->expects($this->any())
      ->method('isUploadedFile')
      ->withAnyParameters()
      ->will($this->returnValue($isUploadedFile));

    $fileSystem = $this->createMock(\Papaya\File\System\Factory::class);
    $fileSystem
      ->expects($this->any())
      ->method('getFile')
      ->withAnyParameters()
      ->will($this->returnValue($file));

    return $fileSystem;
  }

}
