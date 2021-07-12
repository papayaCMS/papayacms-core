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

namespace Papaya\CMS;

use Papaya\Configuration\Storage as ConfigurationStorage;

require_once __DIR__.'/../../../bootstrap.php';

class CMSConfigurationTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\CMSConfiguration::__construct
   */
  public function testConstructor() {
    $configuration = new CMSConfiguration();
    $this->assertNotEmpty(
      iterator_to_array($configuration->getIterator())
    );
  }

  /**
   * @covers \Papaya\CMS\CMSConfiguration::getOptionsList
   */
  public function testGetOptionsList() {
    $configuration = new CMSConfiguration();
    $this->assertNotEmpty(
      $configuration->getOptionsList()
    );
  }

  /**
   * @covers \Papaya\CMS\CMSConfiguration::loadAndDefine
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testLoadAndDefineExpectingFalse() {
    $storage = $this->createMock(ConfigurationStorage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(FALSE));
    $configuration = new CMSConfiguration();
    $configuration->storage($storage);
    $this->assertFalse($configuration->loadAndDefine());
  }

  /**
   * @covers \Papaya\CMS\CMSConfiguration::loadAndDefine
   * @covers \Papaya\CMS\CMSConfiguration::defineConstants
   * @covers \Papaya\CMS\CMSConfiguration::setupPaths
   * @covers \Papaya\CMS\CMSConfiguration::defineDatabaseTables
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testLoadAndDefine() {
    $storage = $this->createMock(ConfigurationStorage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new \ArrayIterator(array())));
    $configuration = new CMSConfiguration();
    $configuration->storage($storage);
    $this->assertTrue($configuration->loadAndDefine());
  }

  /**
   * @covers \Papaya\CMS\CMSConfiguration::setupPaths
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testSetupPathsDefaultLocal() {
    $configuration = new CMSConfiguration();
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
   * @covers \Papaya\CMS\CMSConfiguration::setupPaths
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testSetupPathsLocal() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root/';

    $configuration = new CMSConfiguration();
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
   * @covers \Papaya\CMS\CMSConfiguration::setupPaths
   * @preserveGlobalState disabled
   * @runInSeparateProcess
   */
  public function testSetupPathsAwsS3() {
    $_SERVER['DOCUMENT_ROOT'] = '/document/root/';

    $configuration = new CMSConfiguration();
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
