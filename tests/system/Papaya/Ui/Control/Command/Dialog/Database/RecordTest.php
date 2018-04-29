<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiControlCommandDialogDatabaseRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::__construct
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::record
  */
  public function testRecordGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $command->record($record = $this->createMock(PapayaDatabaseInterfaceRecord::class));
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::createDialog
  */
  public function testCreateSaveDialog() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $command->papaya($this->mockPapaya()->application());
    /** @var PapayaUiDialogDatabaseSave $dialog */
    $dialog = $command->dialog();
    $this->assertInstanceOf(PapayaUiDialogDatabaseSave::class, $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::createDialog
  */
  public function testCreateDeleteDialog() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaUiControlCommandDialogDatabaseRecord(
      $record,
      PapayaUiControlCommandDialogDatabaseRecord::ACTION_DELETE
    );
    $command->papaya($this->mockPapaya()->application());
    /** @var PapayaUiDialogDatabaseDelete $dialog */
    $dialog = $command->dialog();
    $this->assertInstanceOf(PapayaUiDialogDatabaseDelete::class, $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }
}
