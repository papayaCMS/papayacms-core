<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesReferenceCommandChangeTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::createDialog
  */
  public function testCreateDialog() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will(
        $this->returnValue($this->getRecordFixture(array('sourceId' => 21,'targetId' => 42)))
      );

    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(2, $dialog->fields);
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
    $this->assertTrue(isset($command->callbacks()->onExecuteFailed));
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::createDialog
  */
  public function testCreateDialogWithoutSourceId() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will(
        $this->returnValue($this->getRecordFixture(array('sourceId' => 0,'targetId' => 42)))
      );

    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(2, $dialog->fields);
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::createDialog
  */
  public function testCreateDialogWhileSourceIdEqualsPageId() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(21));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will(
        $this->returnValue($this->getRecordFixture(array('sourceId' => 21,'targetId' => 42)))
      );

    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(2, $dialog->fields);
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::validateTarget
  * @covers PapayaAdministrationPagesReferenceCommandChange::sortAsc
  */
  public function testValidateTargetExpectsTrue() {
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $key
      ->expects($this->once())
      ->method('getProperties')
      ->will($this->returnValue(array('sourceId' => 21,'targetId' => 42)));
    $record = $this->getRecordFixture(array('sourceId' => 42,'targetId' => 21));
    $record
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $this->assertTrue(
      $command->validateTarget($this->createMock(stdClass::class), $record)
    );
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::validateTarget
  * @covers PapayaAdministrationPagesReferenceCommandChange::sortAsc
  */
  public function testValidateTargetExpectingFalse() {
    $field = $this->createMock(PapayaUiDialogField::class);
    $field
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(PapayaFilterExceptionCallbackFailed::class));
    $key = $this->createMock(PapayaDatabaseInterfaceKey::class);
    $key
      ->expects($this->once())
      ->method('getProperties')
      ->will($this->returnValue(array('sourceId' => 21,'targetId' => 42)));
    $record = $this->getRecordFixture(array('sourceId' => 21,'targetId' => 23));
    $record
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $record
      ->expects($this->once())
      ->method('exists')
      ->with(21, 23)
      ->will($this->returnValue(TRUE));
    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $context = new stdClass();
    $context->targetIdField = $field;
    $this->assertFalse(
      $command->validateTarget($context, $record)
    );
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::dispatchSavedMessage
  */
  public function testDispatchSavedMessage() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplayTranslated::class));
    $application = $this->mockPapaya()->application(
      array(
        'Messages' => $messages
      )
    );
    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $command->papaya($application);
    $command->dispatchSavedMessage();
  }

  /**
  * @covers PapayaAdministrationPagesReferenceCommandChange::dispatchErrorMessage
  */
  public function testDispatchErrorMessage() {
    $errors = $this->createMock(PapayaUiDialogErrors::class);
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array('field')));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiDialog $dialog */
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplayTranslated::class));
    $application = $this->mockPapaya()->application(
      array(
        'Messages' => $messages
      )
    );
    $command = new PapayaAdministrationPagesReferenceCommandChange();
    $command->papaya($application);
    $command->dispatchErrorMessage(new stdClass, $dialog);
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageReference
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(PapayaContentPageReference::class);
    $record
      ->expects($this->any())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    $record
      ->expects($this->any())
      ->method('save')
      ->will(
        $this->returnValue(TRUE)
      );
    $record
      ->expects($this->any())
      ->method('__get')
      ->withAnyParameters()
      ->willReturnCallback(
        function($name) use ($data) {
          return $data[$name];
        }
      );
    return $record;
  }
}
