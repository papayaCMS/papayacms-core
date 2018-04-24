<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginEditableAggregationTest extends PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new PapayaPluginEditableAggregation_TestProxy();
    $plugin->content($content = $this->createMock(PapayaPluginEditableContent::class));
    $this->assertSame($content, $plugin->content());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new PapayaPluginEditableAggregation_TestProxy();
    $content = $plugin->content();
    $this->assertInstanceOf(PapayaPluginEditableContent::class, $content);
    $this->assertSame($content, $plugin->content());
    $this->assertInstanceOf(PapayaPluginEditor::class, $content->editor());
  }

}

class PapayaPluginEditableAggregation_TestProxy implements PapayaPluginEditable {

  use PapayaPluginEditableAggregation;

  public function createEditor(PapayaPluginEditableContent $content) {
    return new PapayaAdministrationPluginEditorDialog($content);
  }
}

