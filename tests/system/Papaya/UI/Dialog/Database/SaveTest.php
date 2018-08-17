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

  class SaveTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Dialog\Database\Save::execute
     */
    public function testExecuteExpectingTrue() {
      $callbacks = $this
        ->getMockBuilder(Callbacks::class)
        ->disableOriginalConstructor()
        ->setMethods(array('onBeforeSave'))
        ->getMock();
      $callbacks
        ->expects($this->once())
        ->method('onBeforeSave')
        ->with($this->isInstanceOf(\Papaya\Database\Interfaces\Record::class))
        ->will($this->returnValue(TRUE));
      $record = $this->getRecordFixture();
      $record
        ->expects($this->atLeastOnce())
        ->method('assign');
      $record
        ->expects($this->any())
        ->method('save')
        ->will($this->returnValue(TRUE));
      $dialog = new Save_TestProxy($record);
      $dialog->callbacks($callbacks);
      $this->assertTrue($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Database\Save::execute
     */
    public function testExecuteBlockedByCallbackExpectingFalse() {
      $callbacks = $this
        ->getMockBuilder(Callbacks::class)
        ->disableOriginalConstructor()
        ->setMethods(array('onBeforeSave'))
        ->getMock();
      $callbacks
        ->expects($this->once())
        ->method('onBeforeSave')
        ->with($this->isInstanceOf(\Papaya\Database\Interfaces\Record::class))
        ->will($this->returnValue(FALSE));
      $record = $this->getRecordFixture();
      $record
        ->expects($this->atLeastOnce())
        ->method('assign');
      $dialog = new Save_TestProxy($record);
      $dialog->callbacks($callbacks);
      $this->assertFalse($dialog->execute());
    }

    /**
     * @covers \Papaya\UI\Dialog\Database\Save::execute
     */
    public function testExecuteNoSubmitExpectingFalse() {
      $record = $this->getRecordFixture();
      $dialog = new Save_TestProxy($record);
      $dialog->_isSubmittedResult = FALSE;
      $this->assertFalse($dialog->execute());
    }

    /**************************
     * Fixtures
     **************************/

    /**
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record
     */
    public function getRecordFixture(array $data = array()) {
      $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
      $record
        ->expects($this->any())
        ->method('toArray')
        ->will(
          $this->returnValue($data)
        );
      return $record;
    }
  }

  class Save_TestProxy extends Save {
    public $_isSubmittedResult = TRUE;
  }
}
