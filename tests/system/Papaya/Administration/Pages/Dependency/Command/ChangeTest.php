<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaAdministrationPagesDependencyCommandChangeTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::createCondition
  */
  public function testCreateCondition() {
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $condition = $command->createCondition();
    $this->assertInstanceOf(
      'PapayaUiControlCommandCondition', $condition
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyCommandChange::validatePageId
  */
  public function testValidatePageIdExpectingFalse() {
    $owner = $this->getMock('PapayaAdministrationPagesDependencyChanger');
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
    $owner = $this->getMock('PapayaAdministrationPagesDependencyChanger');
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
    $owner = $this->getMock('PapayaAdministrationPagesDependencyChanger');
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
    $owner = $this->getMock('PapayaAdministrationPagesDependencyChanger');
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


    $synchronizations = $this->getSynchronizationsFixture();
    $command = new PapayaAdministrationPagesDependencyCommandChange();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertEquals(3, count($dialog->fields));
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
      ->getMockBuilder('PapayaUiDialogField')
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->originIdField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf('PapayaFilterException'));
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
      ->getMockBuilder('PapayaUiDialogField')
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->originIdField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf('PapayaFilterException'));
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
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $databaseResult = $this->getMock('PapayaDatabaseResult');
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
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('queryFmt'))
      ->disableOriginalConstructor()
      ->getMock();
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
      ->getMockBuilder('PapayaUiDialogField')
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->synchronizationField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf('PapayaFilterException'));
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
    $databaseResult = $this->getMock('PapayaDatabaseResult');
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
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->setMethods(array('queryFmt'))
      ->disableOriginalConstructor()
      ->getMock();
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
    $context->dependency = $dependency = $this->getMock('PapayaContentPageDependency');
    $context->synchronizations =
      $synchronizations =
      $this->getMock('PapayaAdministrationPagesDependencySynchronizations');
    $synchronizations
      ->expects($this->once())
      ->method('synchronizeDependency')
      ->with($this->isInstanceOf('PapayaContentPageDependency'));

    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplayTranslated'));
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
    $errors = $this->getMock('PapayaUiDialogErrors');
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array('field')));
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplayTranslated'));
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

  public function getRecordFixture($data = array()) {
    $this->_dependencyRecordData = $data;
    $record = $this->getMock('PapayaContentPageDependency');
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
      ->will($this->returnCallback(array($this, 'callbackRecordData')));
    $record
      ->expects($this->any())
      ->method('isDependency')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackOriginIdStatus')));
    return $record;
  }

  public function callbackRecordData($name) {
    return $this->_dependencyRecordData[$name];
  }

  public function callbackOriginIdStatus($id) {
    $isOrigin = array(
      21 => TRUE,
      42 => FALSE
    );
    return $isOrigin[$id];
  }

  public function getSynchronizationsFixture() {
    $sychronizations = $this->getMock('PapayaAdministrationPagesDependencySynchronizations');
    $sychronizations
      ->expects($this->any())
      ->method('getList')
      ->will(
        $this->returnValue(array(23 => 'Test'))
      );
    return $sychronizations;
  }
}
