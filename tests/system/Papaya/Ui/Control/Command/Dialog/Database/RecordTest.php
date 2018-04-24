<?php
require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiControlCommandDialogDatabaseRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::__construct
  */
  public function testConstructor() {
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::record
  */
  public function testRecordGetAfterSet() {
    $command = new PapayaUiControlCommandDialogDatabaseRecord(
      $this->createMock(PapayaDatabaseInterfaceRecord::class)
    );
    $command->record($record = $this->createMock(PapayaDatabaseInterfaceRecord::class));
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::createDialog
  */
  public function testCreateSaveDialog() {
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $command->papaya($this->mockPapaya()->application());
    $dialog = $command->dialog();
    $this->assertInstanceOf(PapayaUiDialogDatabaseSave::class, $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::createDialog
  */
  public function testCreateDeleteDialog() {
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord(
      $record,
      PapayaUiControlCommandDialogDatabaseRecord::ACTION_DELETE
    );
    $command->papaya($this->mockPapaya()->application());
    $dialog = $command->dialog();
    $this->assertInstanceOf(PapayaUiDialogDatabaseDelete::class, $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }
}
