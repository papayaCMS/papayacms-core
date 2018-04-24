<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPluginEditorGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPluginEditorGroup
   */
  public function testAppendToWithOneEditor() {
    $context = new PapayaRequestParameters();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);

    $editorGroup = new PapayaAdministrationPluginEditorGroup($content);
    $editorGroup->papaya($this->mockPapaya()->application());

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditor $editor */
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
      // language=xml
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);

    $editorGroup = new PapayaAdministrationPluginEditorGroup($content);
    $editorGroup->papaya($this->mockPapaya()->application());
    $editorGroup->context($context);

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditor $editor */
    $editor = $this->createMock(PapayaPluginEditor::class);
    $editor
      ->expects($this->any())
      ->method('context')
      ->willReturn($context);

    $editorGroup->add($editor, 'TEST CAPTION');

    $this->assertXmlFragmentEqualsXmlFragment(
      // language=xml
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);

    $editorGroup = new PapayaAdministrationPluginEditorGroup($content, 'dialog-index');
    $editorGroup->papaya(
      $this->mockPapaya()->application(
        array('request' => $this->mockPapaya()->request(array('dialog-index' => 1)))
      )
    );
    $editorGroup->parameters($context);

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditor $editorOne */
    $editorOne = $this->createMock(PapayaPluginEditor::class);
    $editorOne
      ->expects($this->never())
      ->method('context');
    $editorOne
      ->expects($this->never())
      ->method('appendTo');
    $editorGroup->add($editorOne, 'ONE', 'image1');

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditor $editorTwo */
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
      // language=xml
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editorGroup = new PapayaAdministrationPluginEditorGroup($content);
    $editorGroup->papaya($this->mockPapaya()->application());

    $this->expectException(\LogicException::class);
    $editorGroup->getXml();
  }
}