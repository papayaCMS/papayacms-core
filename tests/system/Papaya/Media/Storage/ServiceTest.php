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

namespace Papaya\Media\Storage {

  require_once __DIR__.'/../../../../bootstrap.php';

  class ServiceTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Media\Storage\Service::__construct
     */
    public function testConstructorWithConfiguration() {
      $configuration = $this->createMock(\Papaya\Configuration::class);
      $service = new Service_TestProxy($configuration);
      $this->assertSame($configuration, $service->configurationBuffer);
    }

    /**
     * @covers \Papaya\Media\Storage\Service::__construct
     */
    public function testConstructorWithoutConfiguration() {
      $service = new Service_TestProxy();
      $this->assertNull($service->configurationBuffer);
    }
  }

  class Service_TestProxy extends Service {

    public $configurationBuffer;

    public function setConfiguration(\Papaya\Configuration $configuration) {
      $this->configurationBuffer = $configuration;
    }

    /*
    * Implement abstract methods
    */

    public function verifyConfiguration() {
    }

    public function browse($storageGroup, $startsWith = '') {
    }

    public function store(
      $storageGroup, $storageId, $content, $mimeType = 'application/octet-stream', $isPublic = FALSE
    ) {
    }

    public function storeLocalFile(
      $storageGroup, $storageId, $filename, $mimeType = 'application/octet-stream', $isPublic = FALSE) {
    }

    public function remove($storageGroup, $storageId) {
    }

    public function exists($storageGroup, $storageId) {
    }

    public function allowPublic() {
    }

    public function isPublic($storageGroup, $storageId, $mimeType) {
    }

    public function setPublic($storageGroup, $storageId, $isPublic, $mimeType) {
    }

    public function get($storageGroup, $storageId) {
    }

    public function getURL($storageGroup, $storageId, $mimeType) {
    }

    public function getLocalFile($storageGroup, $storageId) {
    }

    public function output(
      $storageGroup, $storageId, $rangeFrom = 0, $rangeTo = 0, $bufferSize = 1024) {
    }
  }
}

