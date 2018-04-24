<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginFilterAggregationTest extends PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new PapayaPluginFilterAggregation_TestProxy(
      $page = $this->createMock(PapayaUiContentPage::class)
    );
    $plugin->filters($content = $this->createMock(PapayaPluginFilterContent::class));
    $this->assertSame($content, $plugin->filters());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new PapayaPluginFilterAggregation_TestProxy(
      $page = $this->createMock(PapayaUiContentPage::class)
    );
    $content = $plugin->filters();
    $this->assertInstanceOf(PapayaPluginFilterContent::class, $content);
    $this->assertSame($content, $plugin->filters());
  }

}

class PapayaPluginFilterAggregation_TestProxy {

  use PapayaPluginFilterAggregation;

  public function __construct($page) {
    $this->_page = $page;
  }

}

