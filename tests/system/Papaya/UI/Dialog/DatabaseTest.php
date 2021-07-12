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

namespace Papaya\UI\Dialog {

  require_once __DIR__.'/../../../../bootstrap.php';

  class DatabaseTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\Dialog\Database::__construct
     * @covers \Papaya\UI\Dialog\Database::record
     */
    public function testConstructorAndRecord() {
      $record = $this->getRecordFixture(array('foo' => 'bar'));
      $dialog = new Database_TestProxy($record);
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
        ->getMockBuilder(Database\Callbacks::class)
        ->disableOriginalConstructor()
        ->getMock();
      $dialog = new Database_TestProxy($this->getRecordFixture());
      $this->assertSame(
        $callbacks, $dialog->callbacks($callbacks)
      );
    }

    /**
     * @covers \Papaya\UI\Dialog\Database::callbacks
     */
    public function testCallbacksGetImplicitCreate() {
      $dialog = new Database_TestProxy($this->getRecordFixture());
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record
     */
    public function getRecordFixture(array $data = array()) {
      $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
      $record
        ->expects($this->once())
        ->method('toArray')
        ->will(
          $this->returnValue($data)
        );
      return $record;
    }
  }

  class Database_TestProxy extends Database {

  }
}
