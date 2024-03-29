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

namespace Papaya\UI\Dialog\Database {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class DeleteTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Dialog\Database\Delete::execute
     */
    public function testExecuteExpectingTrue() {
      $callbacks = $this
        ->getMockBuilder(Callbacks::class)
        ->disableOriginalConstructor()
        ->setMethods(array('onBeforeDelete'))
        ->getMock();
      $callbacks
        ->expects($this->once())
        ->method('onBeforeDelete')
        ->with($this->isInstanceOf(\Papaya\Database\BaseObject\Record::class))
        ->will($this->returnValue(TRUE));
      $record = $this->getRecordFixture();
      $record
        ->expects($this->once())
        ->method('delete')
        ->will($this->returnValue(TRUE));
      $dialog = new Delete_TestProxy($record);
      $dialog->callbacks($callbacks);
      $this->assertTrue($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Database\Delete::execute
     */
    public function testExecuteBlockedByCallbackExpectingFalse() {
      $callbacks = $this
        ->getMockBuilder(Callbacks::class)
        ->disableOriginalConstructor()
        ->setMethods(array('onBeforeDelete'))
        ->getMock();
      $callbacks
        ->expects($this->once())
        ->method('onBeforeDelete')
        ->with($this->isInstanceOf(\Papaya\Database\BaseObject\Record::class))
        ->will($this->returnValue(FALSE));
      $record = $this->getRecordFixture();
      $record
        ->expects($this->never())
        ->method('delete');
      $dialog = new Delete_TestProxy($record);
      $dialog->callbacks($callbacks);
      $this->assertFalse($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Database\Delete::execute
     */
    public function testExecuteNoSubmitExpectingFalse() {
      $dialog = new Delete_TestProxy($this->getRecordFixture());
      $dialog->_isSubmittedResult = FALSE;
      $this->assertFalse($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Database\Delete::appendTo
     */
    public function testAppendTo() {
      $dialog = new Delete_TestProxy($this->getRecordFixture());
      $dialog->papaya($this->mockPapaya()->application());
      $dialog->options->useToken = FALSE;
      $dialog->_executionResult = FALSE;
      $dialog->_isSubmittedResult = FALSE;
      $this->assertNotEquals(
        '',
        $dialog->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Database\Delete::appendTo
     */
    public function testAppendToExecutionBlocksOutput() {
      $dialog = new Delete_TestProxy($this->getRecordFixture());
      $dialog->_executionResult = TRUE;
      $this->assertEquals(
        '',
        $dialog->getXML()
      );
    }

    /**************************
     * Fixtures
     **************************/

    /**
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\BaseObject\Record
     */
    public function getRecordFixture(array $data = array()) {
      $record = $this->createMock(\Papaya\Database\BaseObject\Record::class);
      $record
        ->expects($this->once())
        ->method('toArray')
        ->will(
          $this->returnValue($data)
        );
      return $record;
    }
  }

  class Delete_TestProxy extends Delete {
    public $_isSubmittedResult = TRUE;
    public $_executionResult;
  }
}
