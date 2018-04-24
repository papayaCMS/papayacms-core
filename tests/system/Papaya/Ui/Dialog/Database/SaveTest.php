<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogDatabaseSaveTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogDatabaseSave::execute
  */
  public function testExecuteExpectingTrue() {
    $callbacks = $this
      ->getMockBuilder(PapayaUiDialogDatabaseCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onBeforeSave'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeSave')
      ->with($this->isInstanceOf(PapayaDatabaseInterfaceRecord::class))
      ->will($this->returnValue(TRUE));
    $record = $this->getRecordFixture();
    $record
      ->expects($this->atLeastOnce())
      ->method('assign');
    $record
      ->expects($this->any())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $dialog = new PapayaUiDialogDatabaseSave_TestProxy($record);
    $dialog->callbacks($callbacks);
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogDatabaseSave::execute
  */
  public function testExecuteBlockedByCallbackExpectingFalse() {
    $callbacks = $this
      ->getMockBuilder(PapayaUiDialogDatabaseCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onBeforeSave'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeSave')
      ->with($this->isInstanceOf(PapayaDatabaseInterfaceRecord::class))
      ->will($this->returnValue(FALSE));
    $record = $this->getRecordFixture();
    $record
      ->expects($this->atLeastOnce())
      ->method('assign');
    $dialog = new PapayaUiDialogDatabaseSave_TestProxy($record);
    $dialog->callbacks($callbacks);
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogDatabaseSave::execute
  */
  public function testExecuteNoSubmitExpectingFalse() {
    $record = $this->getRecordFixture();
    $dialog = new PapayaUiDialogDatabaseSave_TestProxy($record);
    $dialog->_isSubmittedResult = FALSE;
    $this->assertFalse($dialog->execute());
  }

  /**************************
  * Fixtures
  **************************/

  public function getRecordFixture($data = array()) {
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $record
      ->expects($this->any())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    return $record;
  }
}

class PapayaUiDialogDatabaseSave_TestProxy extends PapayaUiDialogDatabaseSave {
  public $_isSubmittedResult = TRUE;
}
