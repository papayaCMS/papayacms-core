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

use Papaya\Database\BaseObject\Record;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogDatabaseDeleteTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogDatabaseDelete::execute
  */
  public function testExecuteExpectingTrue() {
    $callbacks = $this
      ->getMockBuilder(\PapayaUiDialogDatabaseCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onBeforeDelete'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeDelete')
      ->with($this->isInstanceOf(Record::class))
      ->will($this->returnValue(TRUE));
    $record = $this->getRecordFixture();
    $record
      ->expects($this->once())
      ->method('delete')
      ->will($this->returnValue(TRUE));
    $dialog = new \PapayaUiDialogDatabaseDelete_TestProxy($record);
    $dialog->callbacks($callbacks);
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers \PapayaUiDialogDatabaseDelete::execute
  */
  public function testExecuteBlockedByCallbackExpectingFalse() {
    $callbacks = $this
      ->getMockBuilder(\PapayaUiDialogDatabaseCallbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onBeforeDelete'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onBeforeDelete')
      ->with($this->isInstanceOf(Record::class))
      ->will($this->returnValue(FALSE));
    $record = $this->getRecordFixture();
    $record
      ->expects($this->never())
      ->method('delete');
    $dialog = new \PapayaUiDialogDatabaseDelete_TestProxy($record);
    $dialog->callbacks($callbacks);
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \PapayaUiDialogDatabaseDelete::execute
  */
  public function testExecuteNoSubmitExpectingFalse() {
    $dialog = new \PapayaUiDialogDatabaseDelete_TestProxy($this->getRecordFixture());
    $dialog->_isSubmittedResult = FALSE;
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \PapayaUiDialogDatabaseDelete::appendTo
  */
  public function testAppendTo() {
    $dialog = new \PapayaUiDialogDatabaseDelete_TestProxy($this->getRecordFixture());
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
  * @covers \PapayaUiDialogDatabaseDelete::appendTo
  */
  public function testAppendToExecutionBlocksOutput() {
    $dialog = new \PapayaUiDialogDatabaseDelete_TestProxy($this->getRecordFixture());
    $dialog->_executionResult = TRUE;
    $this->assertEquals(
      '',
      $dialog->getXml()
    );
  }

  /**************************
  * Fixtures
  **************************/

  /**
   * @param array $data
   * @return PHPUnit_Framework_MockObject_MockObject|Record
   */
  public function getRecordFixture(array $data = array()) {
    $record = $this->createMock(Record::class);
    $record
      ->expects($this->once())
      ->method('toArray')
      ->will(
        $this->returnValue($data)
      );
    return $record;
  }
}

class PapayaUiDialogDatabaseDelete_TestProxy extends \PapayaUiDialogDatabaseDelete {
  public $_isSubmittedResult = TRUE;
  public $_executionResult;
}
