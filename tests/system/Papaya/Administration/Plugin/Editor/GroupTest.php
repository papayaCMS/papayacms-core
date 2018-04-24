<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPluginEditorGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPluginEditorGroup
   */
  public function testAppendToWithOneEditor() {
    $context = new PapayaRequestParameters();
    $content = $this->createMock(PapayaPluginEditableContent::class);

    $editorGroup = new PapayaAdministrationPluginEditorGroup($content);
    $editorGroup->papaya($this->mockPapaya()->application());

    $editor = $this->createMock(PapayaPluginEditor::class);
    $editor
      ->expects($this->once())
      ->method('context')
      ->willReturn($context);
    $editor
      ->expects($this->once())
      ->method('appendTo');

    $editorGroup->add($editor, 'TEST CAPTION');

    $this->assertXmlFragmentEqualsXmlFragment(
      '<toolbar>
          <button down="down" href="http://www.test.tld/test.html?editor_index=0" title="TEST CAPTION"/>
        </toolbar>',
        $editorGroup->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationPluginEditorGroup
   */
  public function testAppendToWithOneEditorAndContextData() {
    $context = new PapayaRequestParameters(array('foo' => 'bar'));
    $content = $this->createMock(PapayaPluginEditableContent::class);

    $editorGroup = new PapayaAdministrationPluginEditorGroup($content);
    $editorGroup->papaya($this->mockPapaya()->application());
    $editorGroup->context($context);

    $editor = $this->createMock(PapayaPluginEditor::class);
    $editor
      ->expects($this->any())
      ->method('context')
      ->willReturn($context);

    $editorGroup->add($editor, 'TEST CAPTION');

    $this->assertXmlFragmentEqualsXmlFragment(
      '<toolbar>
          <button down="down" href="http://www.test.tld/test.html?editor_index=0&amp;foo=bar" title="TEST CAPTION"/>
        </toolbar>',
      $editorGroup->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationPluginEditorGroup
   */
  public function testAppendToWithTwoEditorsSelectingSecond() {
    $context = new PapayaRequestParameters(array('dialog-index' => 1));
    $content = $this->createMock(PapayaPluginEditableContent::class);

    $editorGroup = new PapayaAdministrationPluginEditorGroup($content, 'dialog-index');
    $editorGroup->papaya(
      $this->mockPapaya()->application(
        array('request' => $this->mockPapaya()->request(array('dialog-index' => 1)))
      )
    );
    $editorGroup->parameters($context);

    $editorOne = $this->createMock(PapayaPluginEditor::class);
    $editorOne
      ->expects($this->never())
      ->method('context');
    $editorOne
      ->expects($this->never())
      ->method('appendTo');
    $editorGroup->add($editorOne, 'ONE', 'image1');

    $editorTwo = $this->createMock(PapayaPluginEditor::class);
    $editorTwo
      ->expects($this->once())
      ->method('context')
      ->willReturn($context);
    $editorTwo
      ->expects($this->once())
      ->method('appendTo');
    $editorGroup->add($editorTwo, 'TWO', 'image2');

    $this->assertXmlFragmentEqualsXmlFragment(
      '<toolbar>
        <button href="http://www.test.tld/test.html?dialog-index=0" title="ONE"/>
        <button down="down" href="http://www.test.tld/test.html?dialog-index=1" title="TWO"/>
      </toolbar>',
      $editorGroup->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationPluginEditorGroup
   */
  public function testAppendToWithoutEditorExpectingException() {
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editorGroup = new PapayaAdministrationPluginEditorGroup($content);
    $editorGroup->papaya($this->mockPapaya()->application());

    $this->expectException(\LogicException::class);
    $editorGroup->getXml();
  }
}
