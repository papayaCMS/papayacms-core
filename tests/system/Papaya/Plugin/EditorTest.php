<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaPluginEditorTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginEditor::__construct
   * @covers PapayaPluginEditor::getContent
   */
  public function testConstructorAndGetContent() {
    $content = $this->getMock('PapayaPluginEditableContent');
    $editor = new PapayaPluginEditor_TestProxy($content);
    $this->assertSame($content, $editor->getContent());
  }

  /**
   * @covers PapayaPluginEditor::context
   */
  public function testContextGetAfterSet() {
    $editor = new PapayaPluginEditor_TestProxy($this->getMock('PapayaPluginEditableContent'));
    $editor->context($context = $this->getMock('PapayaRequestParameters'));
    $this->assertSame($context, $editor->context());
  }

  /**
   * @covers PapayaPluginEditor::context
   */
  public function testContextGetImplicitCreate() {
    $editor = new PapayaPluginEditor_TestProxy($this->getMock('PapayaPluginEditableContent'));
    $this->assertInstanceOf('PapayaRequestParameters', $editor->context());
  }

}

class PapayaPluginEditor_TestProxy extends PapayaPluginEditor {

  public function appendTo(PapayaXmlElement $parent) {

  }
}