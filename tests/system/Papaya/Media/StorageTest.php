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

namespace Papaya\Media;
require_once __DIR__.'/../../../bootstrap.php';

class StorageTest extends \Papaya\TestFramework\TestCase {

  public function testGetServiceDefault() {
    $service = Storage::getService();
    $this->assertInstanceOf(Storage\Service::class, $service);
    $serviceTwo = Storage::getService();
    $this->assertInstanceOf(Storage\Service::class, $service);
    $this->assertSame($service, $serviceTwo);
  }

  public function testGetServiceInvalid() {
    $this->expectException(\InvalidArgumentException::class);
    Storage::getService('InvalidServiceName');
  }

  public function testGetServiceWithConfiguration() {
    $configuration = $this->mockPapaya()->options();
    $service = Storage::getService('file', $configuration, FALSE);
    $this->assertInstanceOf(Storage\Service::class, $service);
  }

  public function testGetServiceNonStatic() {
    $service = Storage::getService('file', NULL, FALSE);
    $this->assertInstanceOf(Storage\Service::class, $service);
    $serviceTwo = Storage::getService('file', NULL, FALSE);
    $this->assertInstanceOf(Storage\Service::class, $service);
    $this->assertNotSame($service, $serviceTwo);
  }
}
