<?php
require_once(dirname(__FILE__).'/../../../../../../../bootstrap.php');

class PapayaUiControlCommandDialogDatabaseRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::__construct
  */
  public function testConstructor() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::record
  */
  public function testRecordGetAfterSet() {
    $command = new PapayaUiControlCommandDialogDatabaseRecord(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->record($record = $this->getMock('PapayaDatabaseInterfaceRecord'));
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::createDialog
  */
  public function testCreateSaveDialog() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $command->papaya($this->mockPapaya()->application());
    $dialog = $command->dialog();
    $this->assertInstanceOf('PapayaUiDialogDatabaseSave', $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::createDialog
  */
  public function testCreateDeleteDialog() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $command = new PapayaUiControlCommandDialogDatabaseRecord(
      $record,
      PapayaUiControlCommandDialogDatabaseRecord::ACTION_DELETE
    );
    $command->papaya($this->mockPapaya()->application());
    $dialog = $command->dialog();
    $this->assertInstanceOf('PapayaUiDialogDatabaseDelete', $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }
}