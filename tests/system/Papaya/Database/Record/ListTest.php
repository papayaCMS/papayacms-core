<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseRecordListTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseRecordList::__construct
   */
  public function testConstructor() {
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
    $recordOne = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $recordOne
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $recordTwo = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
    $recordOne = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $recordOne
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $recordTwo = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
    $recordOne = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $recordOne
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(TRUE));
    $recordTwo = $this->createMock(PapayaDatabaseInterfaceRecord::class);
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
    $recordOne = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $recordOne
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(FALSE));
    $recordTwo = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $recordTwo
      ->expects($this->never())
      ->method('delete');
    $list = new PapayaDatabaseRecordList();
    $list[] = $recordOne;
    $list[] = $recordTwo;
    $this->assertFalse($list->delete());
  }
}
