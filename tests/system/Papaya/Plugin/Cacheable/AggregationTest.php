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

use Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginCacheableAggregationTest extends \PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new \PapayaPluginCacheableAggregation_TestProxy();
    $plugin->cacheable($content = $this->createMock(Definition::class));
    $this->assertSame($content, $plugin->cacheable());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new \PapayaPluginCacheableAggregation_TestProxy();
    $plugin->cacheDefinition = $this->createMock(Definition::class);
    $content = $plugin->cacheable();
    $this->assertInstanceOf(Definition::class, $content);
    $this->assertSame($content, $plugin->cacheable());
  }

}

class PapayaPluginCacheableAggregation_TestProxy implements \PapayaPluginCacheable {

  use PapayaPluginCacheableAggregation;

  /**
   * @var \PapayaTestCase
   */
  public $cacheDefinition;

  public function createCacheDefinition() {
    return $this->cacheDefinition;
  }
}

