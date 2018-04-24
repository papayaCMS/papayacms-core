<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginEditableContentTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginEditableContent::getXml
   */
  public function testGetXml() {
    $content = new PapayaPluginEditableContent(array('foo' => 'bar', 'bar' => 'foo'));
    $this->assertXmlStringEqualsXmlString(
      '<data version="2">
        <data-element name="foo">bar</data-element>
        <data-element name="bar">foo</data-element>
      </data>',
      $content->getXml()
    );
  }

  /**
   * @covers PapayaPluginEditableContent::setXml
   */
  public function testSetXml() {
    $content = new PapayaPluginEditableContent();
    $content->setXml(
      '<data version="2">
        <data-element name="foo">bar</data-element>
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $this->assertEquals(
      array('foo' => 'bar', 'bar' => 'foo'),
      (array)$content
    );
  }

  /**
   * @covers PapayaPluginEditableContent::setXml
   */
  public function testSetXmlReplacesAllData() {
    $content = new PapayaPluginEditableContent(array('foo' => 'bar'));
    $content->setXml(
      '<data version="2">
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $this->assertEquals(
      array('bar' => 'foo'),
      (array)$content
    );
  }

  /**
   * @covers PapayaPluginEditableContent::modified
   */
  public function testModfiedIsTrueOnNewObject() {
    $content = new PapayaPluginEditableContent();
    $this->assertTrue($content->modified());
  }
  /**
   * @covers PapayaPluginEditableContent::modified
   */
  public function testModfiedIsFalseAfterSetXml() {
    $content = new PapayaPluginEditableContent();
    $content->setXml('');
    $this->assertFalse($content->modified());
  }

  /**
   * @covers PapayaPluginEditableContent::modified
   */
  public function testModfiedIsTrueAfterChange() {
    $content = new PapayaPluginEditableContent();
    $content->setXml(
      '<data version="2">
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $content['foo'] = 'bar';
    $this->assertTrue($content->modified());
  }

  /**
   * @covers PapayaPluginEditableContent::modified
   */
  public function testModfiedIsFalseForEqualData() {
    $content = new PapayaPluginEditableContent();
    $content->setXml(
      '<data version="2">
        <data-element name="foo">bar</data-element>
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $content->clear();
    $content['bar'] = 'foo';
    $content['foo'] = 'bar';
    $this->assertFalse($content->modified());
  }

  /**
   * @covers PapayaPluginEditableContent::editor
   */
  public function testEditorGetAfterSet() {
    $editor = $this
      ->getMockBuilder(PapayaPluginEditor::class)
      ->disableOriginalConstructor()
      ->getMock();
    $content = new PapayaPluginEditableContent();
    $content->editor($editor);
    $this->assertSame($editor, $content->editor());
  }

  /**
   * @covers PapayaPluginEditableContent::editor
   */
  public function testEditorGetImplicitCreateWithoutCallback() {
    $content = new PapayaPluginEditableContent();
    $this->assertInstanceOf(PapayaPluginEditor::class, $content->editor());
  }

  /**
   * @covers PapayaPluginEditableContent::editor
   */
  public function testEditorImplicitCreateWithInvalidCallbackExpectingException() {
    $content = new PapayaPluginEditableContent();
    $content->callbacks()->onCreateEditor = array($this, 'callbackOnCreateEditorReturnNull');
    $this->setExpectedException(LogicException::class);
    $content->editor();
  }

  public function callbackOnCreateEditorReturnNull() {
    return NULL;
  }

  /**
   * @covers PapayaPluginEditableContent::editor
   */
  public function testEditorImplicitCreateUsingCallback() {
    $content = new PapayaPluginEditableContent();
    $content->callbacks()->onCreateEditor = array($this, 'callbackOnCreateEditor');
    $this->assertInstanceOf(PapayaPluginEditor::class, $content->editor());
  }

  public function callbackOnCreateEditor() {
    return $this
      ->getMockBuilder(PapayaPluginEditor::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * @covers PapayaPluginEditableContent::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $content = new PapayaPluginEditableContent();
    $content->callbacks($callbacks = $this->createMock(PapayaPluginEditableContentCallbacks::class));
    $this->assertSame($callbacks, $content->callbacks());
  }

  /**
   * @covers PapayaPluginEditableContent::callbacks
   */
  public function testCallbacksGetImplicitCreate() {
    $content = new PapayaPluginEditableContent();
    $this->assertInstanceOf(PapayaPluginEditableContentCallbacks::class, $content->callbacks());
  }
}
