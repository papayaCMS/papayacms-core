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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordListTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseRecordList::__construct
   */
  public function testConstructor() {
    $record = $this->createMock(Record::class);
    $list = new PapayaDatabaseRecordList();
    $list[] = $record;
    $this->assertEquals(
      array($record), iterator_to_array($list)
    );
  }

  /**
   * @covers PapayaDatabaseRecordList::toArray
   */
  public function testToArray() {
    $record = $this->createMock(Record::class);
    $record
      ->expects($this->once())
      ->method('toArray')
      ->will($this->returnValue(array('foo' => 42)));
    $list = new PapayaDatabaseRecordList();
    $list[] = $record;
    $this->assertEquals(
      array(array('foo' => 42)), $list->toArray()
    );
  }

  /**
   * @covers PapayaDatabaseRecordList::save
   */
  public function testSave() {
    $recordOne = $this->createMock(Record::class);
    $recordOne
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $recordTwo = $this->createMock(Record::class);
    $recordTwo
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $list = new PapayaDatabaseRecordList();
    $list[] = $recordOne;
    $list[] = $recordTwo;
    $this->assertTrue($list->save());
  }

  /**
   * @covers PapayaDatabaseRecordList::save
   */
  public function testSaveWithDatabaseErrorExpectingFalse() {
    $recordOne = $this->createMock(Record::class);
    $recordOne
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $recordTwo = $this->createMock(Record::class);
    $recordTwo
      ->expects($this->never())
      ->method('save');
    $list = new PapayaDatabaseRecordList();
    $list[] = $recordOne;
    $list[] = $recordTwo;
    $this->assertFalse($list->save());
  }

  /**
   * @covers PapayaDatabaseRecordList::delete
   */
  public function testDelete() {
    $recordOne = $this->createMock(Record::class);
    $recordOne
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(TRUE));
    $recordTwo = $this->createMock(Record::class);
    $recordTwo
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(TRUE));
    $list = new PapayaDatabaseRecordList();
    $list[] = $recordOne;
    $list[] = $recordTwo;
    $this->assertTrue($list->delete());
  }

  /**
   * @covers PapayaDatabaseRecordList::delete
   */
  public function testDeleteWithDatabaseErrorExpectingFalse() {
    $recordOne = $this->createMock(Record::class);
    $recordOne
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(FALSE));
    $recordTwo = $this->createMock(Record::class);
    $recordTwo
      ->expects($this->never())
      ->method('delete');
    $list = new PapayaDatabaseRecordList();
    $list[] = $recordOne;
    $list[] = $recordTwo;
    $this->assertFalse($list->delete());
  }
}
