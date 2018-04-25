<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesSetChangeTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::createDialog
   */
  public function testCreateDialogWithoutSetId() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesSetChange($record);
    $command->papaya($this->mockPapaya()->application());

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Add theme set"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="set_edit"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="set_id" value="0"/>
        <input type="hidden" name="confirmation" value="d65f67e66a51189011f2e41f9a30bfc4"/>
        <field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">
          <input type="text" name="title" maxlength="200"/>
        </field>
        <button type="submit" align="right">Add</button>
      </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::createDialog
   */
  public function testCreateDialogWithSetIdLoadsRecord() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Change theme set"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="set_edit"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="set_id" value="42"/>
        <input type="hidden" name="confirmation" value="22ca40c56566acdf383d9279d869454e"/>
        <field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">
          <input type="text" name="title" maxlength="200"/>
        </field>
        <button type="submit" align="right">Save</button>
      </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::createDialog
   */
  public function testCreateDialogWithSetIdLoadRecordFailed() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Add theme set"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="set_edit"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="set_id" value="0"/>
        <input type="hidden" name="confirmation" value="d65f67e66a51189011f2e41f9a30bfc4"/>
        <field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">
          <input type="text" name="title" maxlength="200"/>
        </field>
        <button type="submit" align="right">Add</button>
      </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::callbackSaveValues
   */
  public function testCallbackSaveValues() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplay::class));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesSetChange($record);
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackSaveValues();
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesSetChange::callbackShowError
   */
  public function testCallbackShowError() {
    $errors = $this->createMock(PapayaUiDialogErrors::class);
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiDialog $dialog */
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));

    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplay::class));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesSetChange($record);
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackShowError(new stdClass, $dialog);
  }
}
