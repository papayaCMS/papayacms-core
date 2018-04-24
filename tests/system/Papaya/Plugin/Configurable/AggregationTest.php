<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginConfigurableAggregationTest extends PapayaTestCase {
  public function testContentGetAfterSet() {
    $plugin = new PapayaPluginConfigurableAggregation_TestProxy();
    $plugin->configuration($content = $this->createMock(PapayaObjectParameters::class));
    $this->assertSame($content, $plugin->configuration());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new PapayaPluginConfigurableAggregation_TestProxy();
    $content = $plugin->configuration();
    $this->assertInstanceOf(PapayaObjectParameters::class, $content);
    $this->assertSame($content, $plugin->configuration());
  }
}

class PapayaPluginConfigurableAggregation_TestProxy implements PapayaPluginConfigurable {

  use PapayaPluginConfigurableAggregation;
}

