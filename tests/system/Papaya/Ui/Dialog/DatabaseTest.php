<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiDialogDatabaseTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogDatabase::__construct
  * @covers PapayaUiDialogDatabase::record
  */
  public function testConstructorAndRecord() {
    $record = $this->getRecordFixture(array('foo' => 'bar'));
    $dialog = new PapayaUiDialogDatabase_TestProxy($record);
    $this->assertSame(
      $record, $dialog->record()
    );
    $this->assertEquals(
      array('foo' => 'bar'), $dialog->data()->toArray()
    );
  }

  /**
  * @covers PapayaUiDialogDatabase::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder('PapayaUiDialogDatabaseCallbacks')
      ->disableOriginalConstructor()
      ->getMock();
    $dialog = new PapayaUiDialogDatabase_TestProxy($this->getRecordFixture());
    $this->assertSame(
      $callbacks, $dialog->callbacks($callbacks)
    );
  }

  /**
  * @covers PapayaUiDialogDatabase::callbacks
  */
  public function testCallbacksGetImpliciteCreate() {
    $dialog = new PapayaUiDialogDatabase_TestProxy($this->getRecordFixture());
    $callbacks = $dialog->callbacks();
    $this->assertInstanceOf(
      'PapayaObjectCallbacks', $callbacks
    );
  }

  /**************************
  * Fixtures
  **************************/

  public function getRecordFixture($data = array()) {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $record
      ->expects($this->once())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    return $record;
  }
}

class PapayaUiDialogDatabase_TestProxy extends PapayaUiDialogDatabase {

}