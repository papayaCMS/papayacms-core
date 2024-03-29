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

namespace Papaya\Utility\File;
require_once __DIR__.'/../../../../bootstrap.php';

class PathTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Utility\File\Path::cleanup
   * @dataProvider provideCleanupData
   * @param string $expected
   * @param string $string
   * @param bool $trailingSlash
   */
  public function testCleanup($expected, $string, $trailingSlash = TRUE) {
    $this->assertEquals(
      $expected,
      Path::cleanup($string, $trailingSlash)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::ensureIsAbsolute
   * @dataProvider provideEnsureIsAbsoluteData
   * @param string $expected
   * @param string $string
   */
  public function testEnsureIsAbsolute($expected, $string) {
    $this->assertEquals(
      $expected,
      Path::ensureIsAbsolute($string)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::ensureTrailingSlash
   * @dataProvider provideEnsureTrailingSlashData
   * @param string $expected
   * @param string $string
   */
  public function testEnsureTrailingSlash($expected, $string) {
    $this->assertEquals(
      $expected,
      Path::ensureTrailingSlash($string)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::ensureNoTrailingSlash
   * @dataProvider provideEnsureNoTrailingSlashData
   * @param string $expected
   * @param string $string
   */
  public function testEnsureNoTrailingSlash($expected, $string) {
    $this->assertEquals(
      $expected,
      Path::ensureNoTrailingSlash($string)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getBasePath
   * @backupGlobals
   */
  public function testGetBasePathIncludingDocumentRoot() {
    $_SERVER['DOCUMENT_ROOT'] = NULL;
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/file';
    $this->assertEquals(
      '/path/to/',
      Path::getBasePath(TRUE)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getBasePath
   * @backupGlobals
   */
  public function testGetBasePathExcludingDocumentRoot() {
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/file';
    $_SERVER['DOCUMENT_ROOT'] = '/path';
    $this->assertEquals(
      '/to/',
      Path::getBasePath(FALSE)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getBasePath
   * @backupGlobals
   */
  public function testGetBasePathExcludingDocumentRootWithDeviceLetter() {
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/file';
    $_SERVER['DOCUMENT_ROOT'] = 'c:\\path\\';
    $this->assertEquals(
      '/to/',
      Path::getBasePath(FALSE)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getDocumentRoot
   * @backupGlobals
   */
  public function testGetDocumentRoot() {
    $_SERVER['DOCUMENT_ROOT'] = '/path';
    $this->assertEquals(
      '/path/',
      Path::getDocumentRoot()
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getDocumentRoot
   * @backupGlobals
   */
  public function testGetDocumentRootFromScriptFilename() {
    $_SERVER['DOCUMENT_ROOT'] = NULL;
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/papaya/papaya/file';
    $options = $this->mockPapaya()->options(
      array(
        'PAPAYA_ADMIN_PAGE' => TRUE,
        'PAPAYA_PATH_WEB' => '/papaya/',
      )
    );
    $this->assertEquals(
      '/path/to/',
      Path::getDocumentRoot($options)
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getDocumentRoot
   * @backupGlobals
   */
  public function testGetDocumentRootDefaultReturn() {
    $_SERVER['DOCUMENT_ROOT'] = NULL;
    $_SERVER['SCRIPT_FILENAME'] = NULL;
    $this->assertEquals(
      '/',
      Path::getDocumentRoot()
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getVendorPath
   * @backupGlobals
   */
  public function testGetVendorPath() {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    $_SERVER['SCRIPT_FILENAME'] = NULL;
    $this->assertEquals(
      'vendor/',
      Path::getVendorPath()
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getVendorPath
   * @backupGlobals
   */
  public function testGetVendorPathWithPathNextToDocumentRoot() {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../../../');
    $_SERVER['SCRIPT_FILENAME'] = NULL;
    $this->assertEquals(
      '../vendor/',
      Path::getVendorPath()
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getSourcePath
   * @backupGlobals
   */
  public function testGetSourcePath() {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    $_SERVER['SCRIPT_FILENAME'] = NULL;
    $this->assertEquals(
      'src/',
      Path::getSourcePath()
    );
  }

  /**
   * @covers \Papaya\Utility\File\Path::getSourcePath
   * @backupGlobals
   */
  public function testGetSourcePathWithPathNextToDocumentRoot() {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../../../');
    $_SERVER['SCRIPT_FILENAME'] = NULL;
    $this->assertEquals(
      '../src/',
      Path::getSourcePath()
    );
  }

  public function testClear() {
    $this->createTemporaryDirectory();
    $oldMask = umask(0);
    mkdir($this->_temporaryDirectory.'/GROUP/ELEMENT/', 0777, TRUE);
    umask($oldMask);
    file_put_contents(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      'DATA'
    );
    $this->assertFileExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
    Path::clear($this->_temporaryDirectory);
    $this->assertFileNotExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
    rmdir($this->_temporaryDirectory);
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function provideCleanupData() {
    return array(
      array('/', '/'),
      array('/sample/', '/sample/'),
      array('/sample/', 'sample/'),
      array('/sample/', 'sample//'),
      array('/sample/', '////sample//'),
      array('/foo/bar/', '/foo/bar/'),
      array('/bar/', '/foo/../bar/'),
      array('/baz/', '/foo/bar/../../baz/'),
      array('/foo/baz/', '/foo/bar/.././baz/'),
      array('c:/foo/baz/', 'c:\foo\bar/.././baz/'),
      array('/', '/', FALSE),
      array('/sample', '/sample/', FALSE),
    );
  }

  public static function provideEnsureIsAbsoluteData() {
    return array(
      array('/', '/'),
      array('/sample', '/sample'),
      array('c:/sample', 'c:/sample'),
      array('/sample', 'sample'),
    );
  }

  public static function provideEnsureTrailingSlashData() {
    return array(
      array('/', '/'),
      array('sample/', 'sample/'),
      array('sample/', 'sample'),
    );
  }

  public static function provideEnsureNoTrailingSlashData() {
    return array(
      array('', '/'),
      array('sample', 'sample/'),
      array('sample', 'sample'),
    );
  }
}
