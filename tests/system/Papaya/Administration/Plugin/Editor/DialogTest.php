<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaAdministrationPluginEditorDialogTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPluginEditorDialog::AppendTo
   */
  public function testAppendToWithoutSubmit() {
    $pluginContent = $this->getMock('PapayaPluginEditableContent');
    $pluginContent
      ->expects($this->never())
      ->method('assign');

    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');

    $editor = new PapayaAdministrationPluginEditorDialog($pluginContent);
    $editor->dialog($dialog);
    $editor->getXml();
  }

  /**
   * @covers PapayaAdministrationPluginEditorDialog::appendTo
   */
  public function testAppendToWhileExecuteWasSuccessful() {
    $pluginContent = $this->getMock('PapayaPluginEditableContent');
    $pluginContent
      ->expects($this->once())
      ->method('assign');

    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');

    $editor = new PapayaAdministrationPluginEditorDialog($pluginContent);
    $editor->dialog($dialog);
    $editor->getXml();
  }

  /**
   * @covers PapayaAdministrationPluginEditorDialog::AppendTo
   */
  public function testAppendToWhileExecuteFailed() {
    $pluginContent = $this->getMock('PapayaPluginEditableContent');
    $pluginContent
      ->expects($this->never())
      ->method('assign');

    $dialogErrors = $this->getMock('PapayaUiDialogErrors');
    $dialogErrors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));

    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($dialogErrors));

    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));

    $editor = new PapayaAdministrationPluginEditorDialog($pluginContent);
    $editor->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $editor->dialog($dialog);
    $editor->getXml();
  }

  /**
   * @covers PapayaAdministrationPluginEditorDialog::dialog
   */
  public function testDialogGetAfterSet() {
    $pluginContent = $this->getMock('PapayaPluginEditableContent');
    $editor = new PapayaAdministrationPluginEditorDialog($pluginContent);
    $editor->dialog($dialog = $this->getMock('PapayaUiDialog'));
    $this->assertSame($dialog, $editor->dialog());
  }

  /**
   * @covers PapayaAdministrationPluginEditorDialog::dialog
   * @covers PapayaAdministrationPluginEditorDialog::createDialog
   */
  public function testDialogGetImplicitCreate() {
    $pluginContent = $this->getMock('PapayaPluginEditableContent');
    $pluginContent
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new EmptyIterator()));

    $editor = new PapayaAdministrationPluginEditorDialog($pluginContent);
    $editor->papaya($this->mockPapaya()->application());
    $editor->context(new PapayaRequestParameters(array('context' => 'sample')));

    $this->assertInstanceOf('PapayaUiDialog', $dialog = $editor->dialog());
    $this->assertXmlStringEqualsXmlString(
      '<dialog-box action="http://www.test.tld/test.html" method="post">
         <title caption="Edit content"/>
         <options>
           <option name="USE_CONFIRMATION" value="yes"/>
           <option name="USE_TOKEN" value="yes"/>
           <option name="PROTECT_CHANGES" value="yes"/>
           <option name="CAPTION_STYLE" value="1"/>
           <option name="DIALOG_WIDTH" value="1"/>
           <option name="TOP_BUTTONS" value="yes"/>
           <option name="BOTTOM_BUTTONS" value="yes"/>
         </options>
         <input type="hidden" name="context" value="sample"/>
         <input type="hidden" name="content[confirmation]" value="true"/>
         <input type="hidden" name="content[token]"/>
         <button type="submit" align="right">Save</button>
       </dialog-box>',
      $dialog->getXml()
    );
  }
}