<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyCommandChangeTest extends PapayaTestCase {

  private $_dependencyRecordData;

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::createCondition
  */
  public function testCreateCondition() {
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $condition = $command->createCondition();
    $this->assertInstanceOf(
      PapayaUiControlCommandCondition::class, $condition
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validatePageId
  */
  public function testValidatePageIdExpectingFalse() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('getOriginId')
      ->will($this->returnValue(42));

    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->owner($owner);
    $this->assertFalse($command->validatePageId());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validatePageId
  */
  public function testValidatePageIdExpectingTrue() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(21));
    $owner
      ->expects($this->once())
      ->method('getOriginId')
      ->will($this->returnValue(42));

    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->owner($owner);
    $this->assertTrue($command->validatePageId());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validatePageId
  */
  public function testValidatePageIdWithoutOriginExpectingTrue() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(21));
    $owner
      ->expects($this->once())
      ->method('getOriginId')
      ->will($this->returnValue(NULL));

    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->owner($owner);
    $this->assertTrue($command->validatePageId());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::createDialog
  */
  public function testCreateDialog() {
    $owner = $this->createMock(PapayaAdministrationPagesDependencyChanger::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('dependency')
      ->will(
        $this->returnValue(
          $this->getRecordFixture(
            array('id' => 21,'originId' => 42, 'synchronization' => 63)
          )
        )
      );
    $owner
      ->expects($this->once())
      ->method('synchronizations')
      ->will($this->returnValue($this->getSynchronizationsFixture()));

    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(3, $dialog->fields);
    $this->assertTrue(isset($dialog->callbacks()->onBeforeSave));
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
    $this->assertTrue(isset($command->callbacks()->onExecuteFailed));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validateOriginAndSynchronizations
  */
  public function testValidateOriginAndSynchronizationsExpectingTrue() {
    $record = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => 127
      )
    );
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $this->assertTrue($command->validateOriginAndSynchronizations(new stdClass, $record));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validateOriginAndSynchronizations
  */
  public function testValidateOriginAndSynchronizationsEqualsPageIdExpectingFalse() {
    $context = new stdClass();
    $context->originIdField = $this
      ->getMockBuilder(PapayaUiDialogField::class)
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->originIdField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(PapayaFilterException::class));
    $record = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 21,
        'synchronization' => 127
      )
    );
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $this->assertFalse($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validateOriginAndSynchronizations
  */
  public function testValidateOriginAndSynchronizationsIsDependencyExpectingFalse() {
    $context = new stdClass();
    $context->originIdField = $this
      ->getMockBuilder(PapayaUiDialogField::class)
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->originIdField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(PapayaFilterException::class));
    $record = $this->getRecordFixture(
      array(
        'id' => 42,
        'originId' => 21,
        'synchronization' => 127
      )
    );
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $this->assertFalse($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validateOriginAndSynchronizations
  * @covers PapayaAdministrationPagesDependencyCommandChange::compareViewModules
  */
  public function testValidateOriginAndSynchronizationsWithModuleConflictExpectingFalse() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplay::class));
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('lng_id' => 1, 'module_counter' => 2),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('topic_trans', 'views', 21, 42))
      ->will($this->returnValue($databaseResult));

    $record = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => 63
      )
    );
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $context = new stdClass();
    $context->synchronizationField = $this
      ->getMockBuilder(PapayaUiDialogField::class)
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->synchronizationField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(PapayaFilterException::class));
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->papaya(
      $this->mockPapaya()->application(
        array(
          'messages' => $messages
        )
      )
    );
    $this->assertFalse($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validateOriginAndSynchronizations
  * @covers PapayaAdministrationPagesDependencyCommandChange::compareViewModules
  */
  public function testValidateOriginAndSynchronizationsWithoutModuleConflictExpectingTrue() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('lng_id' => 1, 'module_counter' => 1),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('topic_trans', 'views', 21, 42))
      ->will($this->returnValue($databaseResult));

    $record = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => 63
      )
    );
    $record
      ->expects($this->any())
      ->method('getDatabaseAccess')
      ->will($this->returnValue($databaseAccess));
    $context = new stdClass();
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $this->assertTrue($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::handleExecutionSuccess
  */
  public function testHandleExecutionSuccess() {
    $context = new stdClass();
    $context->dependency = $this->createMock(PapayaContentPageDependency::class);
    $context->synchronizations =
      $synchronizations =
      $this->createMock(PapayaAdministrationPagesDependencySynchronizations::class);
    $synchronizations
      ->expects($this->once())
      ->method('synchronizeDependency')
      ->with($this->isInstanceOf(PapayaContentPageDependency::class));

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
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->papaya($application);
    $command->handleExecutionSuccess($context);
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::dispatchErrorMessage
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
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->papaya($application);
    $command->dispatchErrorMessage(new stdClass, $dialog);
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaContentPageDependency
   */
  public function getRecordFixture(array $data = array()) {
    $this->_dependencyRecordData = $data;
    $record = $this->createMock(PapayaContentPageDependency::class);
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
    $record
      ->expects($this->any())
      ->method('isDependency')
      ->withAnyParameters()
      ->willReturnCallback(
        function($id) {
          $isOrigin = array(
            21 => TRUE,
            42 => FALSE
          );
          return $isOrigin[$id];
        }
      );
    return $record;
  }

  public function getSynchronizationsFixture() {
    $synchronizations = $this->createMock(PapayaAdministrationPagesDependencySynchronizations::class);
    $synchronizations
      ->expects($this->any())
      ->method('getList')
      ->will(
        $this->returnValue(array(23 => 'Test'))
      );
    return $synchronizations;
  }
}
