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

use Papaya\Database\Interfaces\Record;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogDatabaseTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Dialog\Database::__construct
  * @covers \Papaya\UI\Dialog\Database::record
  */
  public function testConstructorAndRecord() {
    $record = $this->getRecordFixture(array('foo' => 'bar'));
    $dialog = new \PapayaUiDialogDatabase_TestProxy($record);
    $this->assertSame(
      $record, $dialog->record()
    );
    $this->assertEquals(
      array('foo' => 'bar'), $dialog->data()->toArray()
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Database::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder(\Papaya\UI\Dialog\Database\Callbacks::class)
      ->disableOriginalConstructor()
      ->getMock();
    $dialog = new \PapayaUiDialogDatabase_TestProxy($this->getRecordFixture());
    $this->assertSame(
      $callbacks, $dialog->callbacks($callbacks)
    );
  }

  /**
  * @covers \Papaya\UI\Dialog\Database::callbacks
  */
  public function testCallbacksGetImpliciteCreate() {
    $dialog = new \PapayaUiDialogDatabase_TestProxy($this->getRecordFixture());
    $callbacks = $dialog->callbacks();
    $this->assertInstanceOf(
      \Papaya\BaseObject\Callbacks::class, $callbacks
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

class PapayaUiDialogDatabase_TestProxy extends \Papaya\UI\Dialog\Database {

}
