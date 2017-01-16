<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaAdministrationThemeEditorChangesSetChangeTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::createDialog
   */
  public function testCreateDialogWithoutSetId() {
    $command = new PapayaAdministrationThemeEditorChangesSetChange(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Add theme set"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="no"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="m"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="cmd" value="set_edit"/>'.
        '<input type="hidden" name="theme"/>'.
        '<input type="hidden" name="set_id" value="0"/>'.
        '<input type="hidden" name="confirmation" value="d65f67e66a51189011f2e41f9a30bfc4"/>'.
        '<field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">'.
          '<input type="text" name="title" maxlength="200"></input>'.
        '</field>'.
        '<button type="submit" align="right">Add</button>'.
      '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::createDialog
   */
  public function testCreateDialogWithSetIdLoadsRecord() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $record
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $command = new PapayaAdministrationThemeEditorChangesSetChange($record);
    $command->papaya($this->mockPapaya()->application());
    $command->parameters(new PapayaRequestParameters(array('set_id' => 42)));

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Change theme set"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="no"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="m"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="cmd" value="set_edit"/>'.
        '<input type="hidden" name="theme"/>'.
        '<input type="hidden" name="set_id" value="42"/>'.
        '<input type="hidden" name="confirmation" value="22ca40c56566acdf383d9279d869454e"/>'.
        '<field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">'.
          '<input type="text" name="title" maxlength="200"></input>'.
        '</field>'.
        '<button type="submit" align="right">Save</button>'.
      '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::createDialog
   */
  public function testCreateDialogWithSetIdLoadRecordFailed() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $record
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(FALSE));
    $command = new PapayaAdministrationThemeEditorChangesSetChange($record);
    $command->papaya($this->mockPapaya()->application());
    $command->parameters(new PapayaRequestParameters(array('set_id' => 42)));

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Add theme set"/>'.
        '<options>'.
          '<option name="USE_CONFIRMATION" value="yes"/>'.
          '<option name="USE_TOKEN" value="no"/>'.
          '<option name="PROTECT_CHANGES" value="yes"/>'.
          '<option name="CAPTION_STYLE" value="1"/>'.
          '<option name="DIALOG_WIDTH" value="m"/>'.
          '<option name="TOP_BUTTONS" value="no"/>'.
          '<option name="BOTTOM_BUTTONS" value="yes"/>'.
        '</options>'.
        '<input type="hidden" name="cmd" value="set_edit"/>'.
        '<input type="hidden" name="theme"/>'.
        '<input type="hidden" name="set_id" value="0"/>'.
        '<input type="hidden" name="confirmation" value="d65f67e66a51189011f2e41f9a30bfc4"/>'.
        '<field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">'.
          '<input type="text" name="title" maxlength="200"></input>'.
        '</field>'.
        '<button type="submit" align="right">Add</button>'.
      '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::callbackSaveValues
   */
  public function testCallbackSaveValues() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $command = new PapayaAdministrationThemeEditorChangesSetChange(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackSaveValues(new stdClass, $this->getMock('PapayaUiDialog'));
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::callbackShowError
   */
  public function testCallbackShowError() {
    $errors = $this->getMock('PapayaUiDialogErrors');
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));

    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $command = new PapayaAdministrationThemeEditorChangesSetChange(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackShowError(new stdClass, $dialog);
  }
}
