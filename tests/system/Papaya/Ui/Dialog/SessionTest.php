<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogSessionTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogSession::__construct
  */
  public function testConstructor() {
    $dialog = new PapayaUiDialogSession();
    $this->assertAttributeSame(
      $dialog, '_sessionIdentifier', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogSession::__construct
  */
  public function testConstructorWithSessionIdentifier() {
    $dialog = new PapayaUiDialogSession('sample_name');
    $this->assertAttributeSame(
      'sample_name', '_sessionIdentifier', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogSession::execute
  */
  public function testExecuteSetSessionVariableExpectingTrue() {
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('getValue')
      ->with('session_identifier')
      ->will($this->returnValue(array('session' => 'value')));
    $session
      ->expects($this->once())
      ->method('setValue')
      ->with('session_identifier', array('session' => 'value'));

    $dialog = new PapayaUiDialogSession_TestProxy('session_identifier');
    $dialog->papaya(
      $this->mockPapaya()->application(
        array('session' => $session)
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogSession::execute
  */
  public function testExecuteSetSessionVariableExpectingFalseWithoutData() {
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('getValue')
      ->with('session_identifier')
      ->will($this->returnValue(FALSE));
    $session
      ->expects($this->never())
      ->method('setValue');

    $dialog = new PapayaUiDialogSession_TestProxy('session_identifier');
    $dialog->_isSubmittedResult = FALSE;
    $dialog->papaya(
      $this->mockPapaya()->application(
        array('session' => $session)
      )
    );
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogSession::reset
  */
  public function testReset() {
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('setValue')
      ->with('session_identifier', NULL);

    $dialog = new PapayaUiDialogSession('session_identifier');
    $dialog->papaya(
      $this->mockPapaya()->application(
        array('session' => $session)
      )
    );
    $dialog->reset();
  }
}

class PapayaUiDialogSession_TestProxy extends PapayaUiDialogSession {
  public $_isSubmittedResult = TRUE;
}
