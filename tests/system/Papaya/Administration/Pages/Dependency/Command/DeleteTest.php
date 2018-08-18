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

namespace Papaya\Administration\Pages\Dependency\Command;

require_once __DIR__.'/../../../../../../bootstrap.php';

class DeleteTest extends \Papaya\TestCase {

  private $_dependencyRecordData;

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Command\Delete::createDialog
   */
  public function testCreateDialog() {
    $owner = $this->createMock(\Papaya\Administration\Pages\Dependency\Changer::class);
    $owner
      ->expects($this->once())
      ->method('getPageId')
      ->will($this->returnValue(42));
    $owner
      ->expects($this->once())
      ->method('dependency')
      ->will($this->returnValue($this->getRecordFixture(array('id' => 21, 'originId' => 42))));

    $command = new Delete();
    $command->owner($owner);
    $dialog = $command->createDialog();
    $this->assertCount(1, $dialog->fields);
    $this->assertTrue(isset($command->callbacks()->onExecuteSuccessful));
  }

  /**
   * @covers \Papaya\Administration\Pages\Dependency\Command\Delete::dispatchDeleteMessage
   */
  public function testDispatchDeleteMessage() {
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
    $command = new Delete();
    $command->papaya($application);
    $command->dispatchDeleteMessage();
  }

  /**************************
   * Fixtures
   **************************/

  /**
   * @param array $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Content\Page\Dependency
   */
  public function getRecordFixture(array $data = array()) {
    $this->_dependencyRecordData = $data;
    $record = $this->createMock(\Papaya\Content\Page\Dependency::class);
    $record
      ->expects($this->any())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    $record
      ->expects($this->any())
      ->method('delete')
      ->will(
        $this->returnValue(TRUE)
      );
    return $record;
  }
}
