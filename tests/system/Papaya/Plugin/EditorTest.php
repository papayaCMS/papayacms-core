<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaPluginEditorTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginEditor::__construct
   * @covers PapayaPluginEditor::getContent
   */
  public function testConstructorAndGetContent() {
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editor = new PapayaPluginEditor_TestProxy($content);
    $this->assertSame($content, $editor->getContent());
  }

  /**
   * @covers PapayaPluginEditor::context
   */
  public function testContextGetAfterSet() {
    $editor = new PapayaPluginEditor_TestProxy($this->createMock(PapayaPluginEditableContent::class));
    $editor->context($context = $this->createMock(PapayaRequestParameters::class));
    $this->assertSame($context, $editor->context());
  }

  /**
   * @covers PapayaPluginEditor::context
   */
  public function testContextGetImplicitCreate() {
    $editor = new PapayaPluginEditor_TestProxy($this->createMock(PapayaPluginEditableContent::class));
    $this->assertInstanceOf(PapayaRequestParameters::class, $editor->context());
  }

}

class PapayaPluginEditor_TestProxy extends PapayaPluginEditor {

  public function appendTo(PapayaXmlElement $parent) {

  }
}
