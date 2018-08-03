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

use Papaya\Administration\Pages\Dependency\Command\Change;
use Papaya\Administration\Pages\Dependency\Changer;
use Papaya\Administration\Pages\Dependency\Synchronizations;
use Papaya\Content\Page\Dependency;
use Papaya\Database\Result;

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyCommandChangeTest extends \PapayaTestCase {

  private $_dependencyRecordData;

  /**
  * @covers Change::createCondition
  */
  public function testCreateCondition() {
    $command = new Change();
    $condition = $command->createCondition();
    $this->assertInstanceOf(
      \Papaya\Ui\Control\Command\Condition::class, $condition
    );
  }

  /**
  * @covers Change::validatePageId
  */
  public function testValidatePageIdExpectingFalse() {
    $owner = $this->createMock(Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('getOriginId')
      ->will($this->returnValue(42));

    $command = new Change();
    $command->owner($owner);
    $this->assertFalse($command->validatePageId());
  }

  /**
  * @covers Change::validatePageId
  */
  public function testValidatePageIdExpectingTrue() {
    $owner = $this->createMock(Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(21));
    $owner
      ->expects($this->once())
      ->method('getOriginId')
      ->will($this->returnValue(42));

    $command = new Change();
    $command->owner($owner);
    $this->assertTrue($command->validatePageId());
  }

  /**
  * @covers Change::validatePageId
  */
  public function testValidatePageIdWithoutOriginExpectingTrue() {
    $owner = $this->createMock(Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(21));
    $owner
      ->expects($this->once())
      ->method('getOriginId')
      ->will($this->returnValue(NULL));

    $command = new Change();
    $command->owner($owner);
    $this->assertTrue($command->validatePageId());
  }

  /**
  * @covers Change::createDialog
  */
  public function testCreateDialog() {
    $owner = $this->createMock(Changer::class);
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

    $command = new Change();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(3, $dialog->fields);
    $this->assertTrue(isset($dialog->callbacks()->onBeforeSave));
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
    $this->assertTrue(isset($command->callbacks()->onExecuteFailed));
  }

  /**
  * @covers Change::validateOriginAndSynchronizations
  */
  public function testValidateOriginAndSynchronizationsExpectingTrue() {
    $record = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 42,
        'synchronization' => 127
      )
    );
    $command = new Change();
    $this->assertTrue($command->validateOriginAndSynchronizations(new stdClass, $record));
  }

  /**
  * @covers Change::validateOriginAndSynchronizations
  */
  public function testValidateOriginAndSynchronizationsEqualsPageIdExpectingFalse() {
    $context = new stdClass();
    $context->originIdField = $this
      ->getMockBuilder(\Papaya\Ui\Dialog\Field::class)
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->originIdField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(\Papaya\Filter\Exception::class));
    $record = $this->getRecordFixture(
      array(
        'id' => 21,
        'originId' => 21,
        'synchronization' => 127
      )
    );
    $command = new Change();
    $this->assertFalse($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers Change::validateOriginAndSynchronizations
  */
  public function testValidateOriginAndSynchronizationsIsDependencyExpectingFalse() {
    $context = new stdClass();
    $context->originIdField = $this
      ->getMockBuilder(\Papaya\Ui\Dialog\Field::class)
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->originIdField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(\Papaya\Filter\Exception::class));
    $record = $this->getRecordFixture(
      array(
        'id' => 42,
        'originId' => 21,
        'synchronization' => 127
      )
    );
    $command = new Change();
    $this->assertFalse($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers Change::validateOriginAndSynchronizations
  * @covers Change::compareViewModules
  */
  public function testValidateOriginAndSynchronizationsWithModuleConflictExpectingFalse() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(Result::FETCH_ASSOC)
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
      ->with($this->isType('string'), array('table_topic_trans', 'table_views', 21, 42))
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
      ->getMockBuilder(\Papaya\Ui\Dialog\Field::class)
      ->disableOriginalConstructor()
      ->getMock();
    $context
      ->synchronizationField
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(\Papaya\Filter\Exception::class));
    $command = new Change();
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
  * @covers Change::validateOriginAndSynchronizations
  * @covers Change::compareViewModules
  */
  public function testValidateOriginAndSynchronizationsWithoutModuleConflictExpectingTrue() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(Result::FETCH_ASSOC)
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
      ->with($this->isType('string'), array('table_topic_trans', 'table_views', 21, 42))
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
    $command = new Change();
    $this->assertTrue($command->validateOriginAndSynchronizations($context, $record));
  }

  /**
  * @covers Change::handleExecutionSuccess
  */
  public function testHandleExecutionSuccess() {
    $context = new stdClass();
    $context->dependency = $this->createMock(Dependency::class);
    $context->synchronizations =
      $synchronizations =
      $this->createMock(Synchronizations::class);
    $synchronizations
      ->expects($this->once())
      ->method('synchronizeDependency')
      ->with($this->isInstanceOf(Dependency::class));

    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display\Translated::class));
    $application = $this->mockPapaya()->application(
      array(
        'Messages' => $messages
      )
    );
    $command = new Change();
    $command->papaya($application);
    $command->handleExecutionSuccess($context);
  }

  /**
  * @covers Change::dispatchErrorMessage
  */
  public function testDispatchErrorMessage() {
    $errors = $this->createMock(\Papaya\Ui\Dialog\Errors::class);
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array('field')));

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Dialog $dialog */
    $dialog = $this->createMock(\Papaya\Ui\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display\Translated::class));
    $application = $this->mockPapaya()->application(
      array(
        'Messages' => $messages
      )
    );
    $command = new Change();
    $command->papaya($application);
    $command->dispatchErrorMessage(new stdClass, $dialog);
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|Dependency
   */
  public function getRecordFixture(array $data = array()) {
    $this->_dependencyRecordData = $data;
    $record = $this->createMock(Dependency::class);
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
    $synchronizations = $this->createMock(Synchronizations::class);
    $synchronizations
      ->expects($this->any())
      ->method('getList')
      ->will(
        $this->returnValue(array(23 => 'Test'))
      );
    return $synchronizations;
  }
}
