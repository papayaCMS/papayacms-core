<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiDialogDatabaseDeleteTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogDatabaseDelete::execute
  */
  public function testExecuteExpectingTrue() {
    $callbacks = $this
      ->getMockBuilder('PapayaUiDialogDatabaseCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(array('onBeforeDelete'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeDelete')
      ->with($this->isInstanceOf('PapayaDatabaseObjectRecord'))
      ->will($this->returnValue(TRUE));
    $record = $this->getRecordFixture();
    $record
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(TRUE));
    $dialog = new PapayaUiDialogDatabaseDelete_TestProxy($record);
    $dialog->callbacks($callbacks);
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogDatabaseDelete::execute
  */
  public function testExecuteBlockedByCallbackExpectingFalse() {
    $callbacks = $this
      ->getMockBuilder('PapayaUiDialogDatabaseCallbacks')
      ->disableOriginalConstructor()
      ->setMethods(array('onBeforeDelete'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeDelete')
      ->with($this->isInstanceOf('PapayaDatabaseObjectRecord'))
      ->will($this->returnValue(FALSE));
    $record = $this->getRecordFixture();
    $record
      ->expects($this->never())
      ->method('delete');
    $dialog = new PapayaUiDialogDatabaseDelete_TestProxy($record);
    $dialog->callbacks($callbacks);
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogDatabaseDelete::execute
  */
  public function testExecuteNoSubmitExpectingFalse() {
    $dialog = new PapayaUiDialogDatabaseDelete_TestProxy($this->getRecordFixture());
    $dialog->_isSubmittedResult = FALSE;
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogDatabaseDelete::appendTo
  */
  public function testAppendTo() {
    $dialog = new PapayaUiDialogDatabaseDelete_TestProxy($this->getRecordFixture());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options->useToken = FALSE;
    $dialog->_executionResult = FALSE;
    $dialog->_isSubmittedResult = FALSE;
    $this->assertNotEquals(
      '',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseDelete::appendTo
  */
  public function testAppendToExecutionBlocksOutput() {
    $dialog = new PapayaUiDialogDatabaseDelete_TestProxy($this->getRecordFixture());
    $dialog->_executionResult = TRUE;
    $this->assertEquals(
      '',
      $dialog->getXml()
    );
  }

  /**************************
  * Fixtures
  **************************/

  public function getRecordFixture($data = array()) {
    $record = $this->getMock('PapayaDatabaseObjectRecord');
    $record
      ->expects($this->once())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    return $record;
  }
}

class PapayaUiDialogDatabaseDelete_TestProxy extends PapayaUiDialogDatabaseDelete {
  public $_isSubmittedResult = TRUE;
  public $_executionResult = NULL;
}