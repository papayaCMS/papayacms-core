<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaDatabaseRecordListTest extends PapayaTestCase {

  /**
   * @covers PapayaDatabaseRecordList::__construct
   */
  public function testConstructor() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
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
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
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
    $recordOne = $this->getMock('PapayaDatabaseInterfaceRecord');
    $recordOne
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $recordTwo = $this->getMock('PapayaDatabaseInterfaceRecord');
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
    $recordOne = $this->getMock('PapayaDatabaseInterfaceRecord');
    $recordOne
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $recordTwo = $this->getMock('PapayaDatabaseInterfaceRecord');
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
    $recordOne = $this->getMock('PapayaDatabaseInterfaceRecord');
    $recordOne
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(TRUE));
    $recordTwo = $this->getMock('PapayaDatabaseInterfaceRecord');
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
    $recordOne = $this->getMock('PapayaDatabaseInterfaceRecord');
    $recordOne
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(FALSE));
    $recordTwo = $this->getMock('PapayaDatabaseInterfaceRecord');
    $recordTwo
      ->expects($this->never())
      ->method('delete');
    $list = new PapayaDatabaseRecordList();
    $list[] = $recordOne;
    $list[] = $recordTwo;
    $this->assertFalse($list->delete());
  }
}
