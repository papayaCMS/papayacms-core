<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginCacheableAggregationTest extends PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new PapayaPluginCacheableAggregation_TestProxy();
    $plugin->cacheable($content = $this->createMock(PapayaCacheIdentifierDefinition::class));
    $this->assertSame($content, $plugin->cacheable());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new PapayaPluginCacheableAggregation_TestProxy();
    $plugin->cacheDefinition = $this->createMock(PapayaCacheIdentifierDefinition::class);
    $content = $plugin->cacheable();
    $this->assertInstanceOf(PapayaCacheIdentifierDefinition::class, $content);
    $this->assertSame($content, $plugin->cacheable());
  }

}

class PapayaPluginCacheableAggregation_TestProxy implements PapayaPluginCacheable {

  use PapayaPluginCacheableAggregation;

  /**
   * @var PapayaTestCase
   */
  public $cacheDefinition;

  public function createCacheDefinition() {
    return $this->cacheDefinition;
  }
}

