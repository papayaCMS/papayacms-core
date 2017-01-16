<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaAdministrationThemeEditorChangesSetRemoveTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetRemove::createDialog
   */
  public function testCreateDialogWithoutSetId() {
    $command = new PapayaAdministrationThemeEditorChangesSetRemove(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Delete theme set"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="no"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="m"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="confirmation" value="true"/>'.
        '<field class="DialogFieldMessage" error="no">'.
          '<message>Theme set not found.</message>'.
        '</field>'.
      '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetRemove::createDialog
   */
  public function testCreateDialogWithSetIdLoadsRecord() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $record
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $command = new PapayaAdministrationThemeEditorChangesSetRemove($record);
    $command->papaya($this->mockPapaya()->application());
    $command->parameters(new PapayaRequestParameters(array('set_id' => 42)));

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Delete theme set"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="no"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="m"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="cmd" value="set_delete"/>'.
        '<input type="hidden" name="theme"/>'.
        '<input type="hidden" name="set_id" value="42"/>'.
        '<input type="hidden" name="confirmation" value="e243360ba3bba3aeae4579dbede9fdda"/>'.
        '<field class="DialogFieldInformation" error="no">'.
          '<message>Delete theme set</message>'.
        '</field>'.
        '<button type="submit" align="right">Delete</button>'.
      '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetRemove::callbackDeleted
   */
  public function testCallbackDeleted() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $command = new PapayaAdministrationThemeEditorChangesSetRemove(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackDeleted(new stdClass, $this->getMock('PapayaUiDialog'));
  }
}
