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

namespace Papaya\CMS\Administration\Pages\Reference\Command;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class ChangeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::createDialog
   */
  public function testCreateDialog() {
    $owner = $this->createMock(\Papaya\CMS\Administration\Pages\Dependency\Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will(
        $this->returnValue($this->getRecordFixture(array('sourceId' => 21, 'targetId' => 42)))
      );

    $command = new Change();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(2, $dialog->fields);
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
    $this->assertTrue(isset($command->callbacks()->onExecuteFailed));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::createDialog
   */
  public function testCreateDialogWithoutSourceId() {
    $owner = $this->createMock(\Papaya\CMS\Administration\Pages\Dependency\Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will(
        $this->returnValue($this->getRecordFixture(array('sourceId' => 0, 'targetId' => 42)))
      );

    $command = new Change();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(2, $dialog->fields);
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::createDialog
   */
  public function testCreateDialogWhileSourceIdEqualsPageId() {
    $owner = $this->createMock(\Papaya\CMS\Administration\Pages\Dependency\Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(21));
    $owner
      ->expects($this->once())
      ->method('reference')
      ->will(
        $this->returnValue($this->getRecordFixture(array('sourceId' => 21, 'targetId' => 42)))
      );

    $command = new Change();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(2, $dialog->fields);
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::validateTarget
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::sortAsc
   */
  public function testValidateTargetExpectsTrue() {
    $key = $this->createMock(\Papaya\Database\Interfaces\Key::class);
    $key
      ->expects($this->once())
      ->method('getProperties')
      ->will($this->returnValue(array('sourceId' => 21, 'targetId' => 42)));
    $record = $this->getRecordFixture(array('sourceId' => 42, 'targetId' => 21));
    $record
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $command = new Change();
    $this->assertTrue(
      $command->validateTarget($this->createMock(\stdClass::class), $record)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::validateTarget
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::sortAsc
   */
  public function testValidateTargetExpectingFalse() {
    $field = $this->createMock(\Papaya\UI\Dialog\Field::class);
    $field
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with($this->isInstanceOf(\Papaya\Filter\Exception\FailedCallback::class));
    $key = $this->createMock(\Papaya\Database\Interfaces\Key::class);
    $key
      ->expects($this->once())
      ->method('getProperties')
      ->will($this->returnValue(array('sourceId' => 21, 'targetId' => 42)));
    $record = $this->getRecordFixture(array('sourceId' => 21, 'targetId' => 23));
    $record
      ->expects($this->once())
      ->method('key')
      ->will($this->returnValue($key));
    $record
      ->expects($this->once())
      ->method('exists')
      ->with(21, 23)
      ->will($this->returnValue(TRUE));
    $command = new Change();
    $context = new \stdClass();
    $context->targetIdField = $field;
    $this->assertFalse(
      $command->validateTarget($context, $record)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::dispatchSavedMessage
   */
  public function testDispatchSavedMessage() {
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
    $command->dispatchSavedMessage();
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Reference\Command\Change::dispatchErrorMessage
   */
  public function testDispatchErrorMessage() {
    $errors = $this->createMock(\Papaya\UI\Dialog\Errors::class);
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array('field')));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Dialog $dialog */
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
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
    $command->dispatchErrorMessage(new \stdClass, $dialog);
  }

  /**************************
   * Fixtures
   **************************/

  /**
   * @param array $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Content\Page\Reference
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(\Papaya\CMS\Content\Page\Reference::class);
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
        function ($name) use ($data) {
          return $data[$name];
        }
      );
    return $record;
  }
}
