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

namespace Papaya\Configuration;

require_once __DIR__.'/../../../bootstrap.php';

class CMSTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Configuration\CMS::__construct
   */
  public function testConstructor() {
    $configuration = new CMS();
    $this->assertNotEmpty(
      iterator_to_array($configuration->getIterator())
    );
  }

  /**
   * @covers \Papaya\Configuration\CMS::getOptionsList
   */
  public function testGetOptionsList() {
    $configuration = new CMS();
    $this->assertNotEmpty(
      $configuration->getOptionsList()
    );
  }

  /**
   * @covers \Papaya\Configuration\CMS::loadAndDefine
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testLoadAndDefineExpectingFalse() {
    $storage = $this->createMock(Storage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(FALSE));
    $configuration = new CMS();
    $configuration->storage($storage);
    $this->assertFalse($configuration->loadAndDefine());
  }

  /**
   * @covers \Papaya\Configuration\CMS::loadAndDefine
   * @covers \Papaya\Configuration\CMS::defineConstants
   * @covers \Papaya\Configuration\CMS::setupPaths
   * @covers \Papaya\Configuration\CMS::defineDatabaseTables
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testLoadAndDefine() {
    $storage = $this->createMock(Storage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array())));
    $configuration = new CMS();
    $configuration->storage($storage);
    $this->assertTrue($configuration->loadAndDefine());
  }

  /**
   * @covers \Papaya\Configuration\CMS::setupPaths
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testSetupPathsDefaultLocal() {
    $configuration = new CMS();
    $configuration->setupPaths();
    $this->assertEquals('cache/', $configuration['PAPAYA_PATH_CACHE']);
    $this->assertEquals('media/', $configuration['PAPAYA_MEDIA_STORAGE_DIRECTORY']);
    $this->assertEquals('', $configuration['PAPAYA_MEDIA_PUBLIC_DIRECTORY']);
    $this->assertEquals('', $configuration['PAPAYA_MEDIA_PUBLIC_URL']);
    $this->assertEquals('media/files/', $configuration['PAPAYA_PATH_MEDIAFILES']);
    $this->assertEquals('media/thumbs/', $configuration['PAPAYA_PATH_THUMBFILES']);
    $this->assertEquals('/templates/', $configuration['PAPAYA_PATH_TEMPLATES']);
    $this->assertEquals('/papaya/', $configuration['PAPAYA_PATHWEB_ADMIN']);
  }

  /**
   * @covers \Papaya\Configuration\CMS::setupPaths
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testSetupPathsLocal() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root/';

    $configuration = new CMS();
    $configuration['PAPAYA_PATH_DATA'] = '/data/path/';
    $configuration['PAPAYA_PATH_PUBLICFILES'] = '/public/files/';
    $configuration->setupPaths();
    $this->assertEquals('/data/path/cache/', $configuration['PAPAYA_PATH_CACHE']);
    $this->assertEquals('/data/path/media/', $configuration['PAPAYA_MEDIA_STORAGE_DIRECTORY']);
    $this->assertEquals(
      '/document/root/public/files/', $configuration['PAPAYA_MEDIA_PUBLIC_DIRECTORY']
    );
    $this->assertEquals('/public/files/', $configuration['PAPAYA_MEDIA_PUBLIC_URL']);
    $this->assertEquals('/data/path/media/files/', $configuration['PAPAYA_PATH_MEDIAFILES']);
    $this->assertEquals('/data/path/media/thumbs/', $configuration['PAPAYA_PATH_THUMBFILES']);
    $this->assertEquals('/data/path/templates/', $configuration['PAPAYA_PATH_TEMPLATES']);
    $this->assertEquals('/papaya/', $configuration['PAPAYA_PATHWEB_ADMIN']);
  }

  /**
   * @covers \Papaya\Configuration\CMS::setupPaths
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testSetupPathsAwsS3() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root/';

    $configuration = new CMS();
    $configuration['PAPAYA_PATH_DATA'] = '/data/path/';
    $configuration['PAPAYA_MEDIA_STORAGE_SERVICE'] = 's3';
    $configuration->setupPaths();
    $this->assertEquals('/data/path/cache/', $configuration['PAPAYA_PATH_CACHE']);
    $this->assertNull($configuration['PAPAYA_MEDIA_STORAGE_DIRECTORY']);
    $this->assertNull($configuration['PAPAYA_MEDIA_PUBLIC_DIRECTORY']);
    $this->assertNull($configuration['PAPAYA_MEDIA_PUBLIC_URL']);
    $this->assertEquals('s3://:@/media/files/', $configuration['PAPAYA_PATH_MEDIAFILES']);
    $this->assertEquals('s3://:@/media/thumbs/', $configuration['PAPAYA_PATH_THUMBFILES']);
    $this->assertEquals('/data/path/templates/', $configuration['PAPAYA_PATH_TEMPLATES']);
    $this->assertEquals('/papaya/', $configuration['PAPAYA_PATHWEB_ADMIN']);
  }
}
