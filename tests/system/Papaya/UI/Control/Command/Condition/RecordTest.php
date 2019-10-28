<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\UI\Control\Command\Condition {

  use Papaya\Database\Interfaces\Key as DatabaseRecordKey;
  use Papaya\Database\Record as DatabaseRecord;
  use Papaya\TestCase;

  /**
   * @covers \Papaya\UI\Control\Command\Condition\Record
   */
  class RecordTest extends TestCase {

    public function testValidateExpectingTrue() {
      $key = $this->createMock(DatabaseRecordKey::class);
      $key
        ->expects($this->once())
        ->method('exists')
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseRecord $record */
      $record = $this->createMock(DatabaseRecord::class);
      $record
        ->method('key')
        ->willReturn($key);

      $condition = new Record($record);
      $this->assertTrue($condition->validate());
    }

    public function testValidateExpectingFalse() {
      $key = $this->createMock(DatabaseRecordKey::class);
      $key
        ->expects($this->once())
        ->method('exists')
        ->willReturn(FALSE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseRecord $record */
      $record = $this->createMock(DatabaseRecord::class);
      $record
        ->method('key')
        ->willReturn($key);

      $condition = new Record($record);
      $this->assertFalse($condition->validate());
    }

  }

}
