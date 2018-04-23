<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginEditableAggregationTest extends PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new PapayaPluginEditableAggregation_TestProxy();
    $plugin->content($content = $this->createMock('PapayaPluginEditableContent'));
    $this->assertSame($content, $plugin->content());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new PapayaPluginEditableAggregation_TestProxy();
    $content = $plugin->content();
    $this->assertInstanceOf('PapayaPluginEditableContent', $content);
    $this->assertSame($content, $plugin->content());
    $this->assertInstanceOf('PapayaPluginEditor', $content->editor());
  }

}

class PapayaPluginEditableAggregation_TestProxy implements PapayaPluginEditable {

  use PapayaPluginEditableAggregation;

  public function createEditor(PapayaPluginEditableContent $content) {
    return new PapayaAdministrationPluginEditorDialog($content);
  }
}

