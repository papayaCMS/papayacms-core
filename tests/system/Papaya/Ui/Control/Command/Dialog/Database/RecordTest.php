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

use Papaya\Database\Interfaces\Record;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiControlCommandDialogDatabaseRecordTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog\Database\Record::__construct
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $command = new \Papaya\Ui\Control\Command\Dialog\Database\Record($record);
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog\Database\Record::record
  */
  public function testRecordGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $command = new \Papaya\Ui\Control\Command\Dialog\Database\Record($record);
    $command->record($record = $this->createMock(Record::class));
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog\Database\Record::createDialog
  */
  public function testCreateSaveDialog() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $command = new \Papaya\Ui\Control\Command\Dialog\Database\Record($record);
    $command->papaya($this->mockPapaya()->application());
    /** @var \Papaya\Ui\Dialog\Database\Save $dialog */
    $dialog = $command->dialog();
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Database\Save::class, $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog\Database\Record::createDialog
  */
  public function testCreateDeleteDialog() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $command = new \Papaya\Ui\Control\Command\Dialog\Database\Record(
      $record,
      \Papaya\Ui\Control\Command\Dialog\Database\Record::ACTION_DELETE
    );
    $command->papaya($this->mockPapaya()->application());
    /** @var \Papaya\Ui\Dialog\Database\Delete $dialog */
    $dialog = $command->dialog();
    $this->assertInstanceOf(\Papaya\Ui\Dialog\Database\Delete::class, $dialog);
    $this->assertSame($command->papaya(), $dialog->papaya());
    $this->assertSame($record, $dialog->record());
  }
}
