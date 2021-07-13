<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya {

  use Papaya\Cache\Configuration as CacheConfiguration;
  use Papaya\Cache\Service as CacheService;

  require_once __DIR__.'/../../bootstrap.php';

  /**
   * @covers \Papaya\Cache
   */
  class CacheTest extends \Papaya\TestFramework\TestCase {

    public function tearDown(): void {
      Cache::reset();
    }

    public function testGetServiceWithDefaults() {
      $options = new CacheConfiguration();
      $this->assertInstanceOf(
        CacheService\File::class,
        Cache::getService($options, FALSE)
      );
    }

    public function testGetServiceInvalid() {
      $options = new CacheConfiguration();
      $options['SERVICE'] = 'InvalidName';
      $this->expectException(\UnexpectedValueException::class);
      Cache::getService($options, FALSE);
    }

    public function testGetServiceEmpty() {
      $options = new CacheConfiguration();
      $options['SERVICE'] = '';
      $this->expectException(\UnexpectedValueException::class);
      Cache::getService($options, FALSE);
    }

    public function testReset() {
      $configuration = new CacheConfiguration();
      Cache::getService($configuration);
      Cache::reset();
      $this->assertEquals(
        [], Cache::getServices()
      );
    }
  }
}
