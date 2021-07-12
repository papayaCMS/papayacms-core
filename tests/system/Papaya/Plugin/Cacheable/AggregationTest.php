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

namespace Papaya\Plugin\Cacheable {

  require_once __DIR__.'/../../../../bootstrap.php';

  class AggregationTest extends \Papaya\TestFramework\TestCase {

    public function testContentGetAfterSet() {
      $plugin = new CacheableAggregation_TestProxy();
      $plugin->cacheable($content = $this->createMock(
        \Papaya\Cache\Identifier\Definition::class)
      );
      $this->assertSame($content, $plugin->cacheable());
    }

    public function testContentGetWithImplicitCreate() {
      $plugin = new CacheableAggregation_TestProxy();
      $plugin->cacheDefinition = $this->createMock(
        \Papaya\Cache\Identifier\Definition::class
      );
      $content = $plugin->cacheable();
      $this->assertInstanceOf(\Papaya\Cache\Identifier\Definition::class, $content);
      $this->assertSame($content, $plugin->cacheable());
    }

  }

  class CacheableAggregation_TestProxy implements \Papaya\Plugin\Cacheable {

    use Aggregation;

    /**
     * @var \Papaya\Cache\Identifier\Definition
     */
    public $cacheDefinition;

    public function createCacheDefinition() {
      return $this->cacheDefinition;
    }
  }
}

