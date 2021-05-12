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

namespace Papaya\UI\Dialog\Database;
/** @noinspection PhpDeprecationInspection */
require_once __DIR__.'/../../../../../bootstrap.php';

class RecordTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::__construct
   */
  public function testConstructor() {
    $dialog = new Record(
      'tablename', 'indexfield', array('datafield' => NULL)
    );
    $this->assertEquals(
      'tablename', $dialog->getTableName()
    );
    $this->assertEquals(
      'indexfield', $dialog->getIdentifierColumn()
    );
    $this->assertEquals(
      array('datafield' => NULL), $dialog->getColumns()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::_getIdentifierValue
   */
  public function testExecuteWithoutIdentifier() {
    $dialog = $this->getDialogFixture(
      array(), FALSE
    );
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      NULL, $dialog->hiddenFields()->get('indexfield')
    );
    $this->assertEquals(
      Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_INSERT, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::_getIdentifierValue
   */
  public function testExecuteWithoutIdentifierAndColumnCheck() {
    $dialog = new Record(
      'tablename',
      'indexfield',
      array(
        'datafield' => NULL,
        'indexfield' => NULL
      )
    );
    $dialog->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      NULL, $dialog->hiddenFields()->get('indexfield')
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::_load
   * @covers \Papaya\UI\Dialog\Database\Record::_getIdentifierValue
   */
  public function testExecuteWithIdentifierLoadsRecord() {
    $dialog = $this->getDialogFixture(
      array('indexfield' => 42), FALSE
    );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('loadRecord')
      ->with(
        'tablename',
        array('datafield_one', 'datafield_two', 'indexfield'),
        array('indexfield' => 42)
      )
      ->will(
        $this->returnValue(
          array(
            'datafield_one' => '23',
            'indexfield' => '42'
          )
        )
      );
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      42, $dialog->hiddenFields()->get('indexfield')
    );
    $this->assertEquals(
      Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_UPDATE, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::_insert
   * @covers \Papaya\UI\Dialog\Database\Record::_compileRecord
   */
  public function testExecuteInsertsRecord() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'tablename',
        'indexfield',
        array('datafield_one' => 'sample', 'datafield_two' => 23)
      )
      ->will($this->returnValue('success'));
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertTrue($dialog->execute());
    $this->assertEquals(
      'success', $dialog->hiddenFields()->get('indexfield')
    );
    $this->assertEquals(
      Record::ACTION_INSERT, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_UPDATE, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::_insert
   * @covers \Papaya\UI\Dialog\Database\Record::_compileRecord
   */
  public function testExecuteInsertFailed() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'tablename',
        'indexfield',
        array('datafield_one' => 'sample', 'datafield_two' => 23)
      )
      ->will($this->returnValue(FALSE));
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_INSERT, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::getDatabaseAction
   * @covers \Papaya\UI\Dialog\Database\Record::getDatabaseActionNext
   */
  public function testExecuteNoPermissionForInsert() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_INSERT, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::_update
   * @covers \Papaya\UI\Dialog\Database\Record::_compileRecord
   */
  public function testExecuteUpdatesRecord() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => 42)
    );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        'tablename',
        array('datafield_one' => 'sample', 'datafield_two' => 23),
        array('indexfield' => 42)
      )
      ->will($this->returnValue(TRUE));
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertTrue($dialog->execute());
    $this->assertEquals(
      42, $dialog->hiddenFields()->get('indexfield')
    );
    $this->assertEquals(
      Record::ACTION_UPDATE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_UPDATE, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::execute
   * @covers \Papaya\UI\Dialog\Database\Record::getDatabaseAction
   * @covers \Papaya\UI\Dialog\Database\Record::getDatabaseActionNext
   */
  public function testExecuteNoPermissionForUpdate() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => 42)
    );
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      Record::ACTION_UPDATE, $dialog->getDatabaseActionNext()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::setPermissionCallback
   */
  public function testSetPermissionCallback() {
    $dialog = new Record('tablename', 'indexfield', array());
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertEquals(
      array($this, 'callbackPermissionFailed'), $dialog->getPermissionCallback()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::checkRecordPermission
   */
  public function testCheckRecordPermissionExpectingTrue() {
    $arguments = [];
    $callback = function () use (&$arguments) {
      $arguments = func_get_args();
      return TRUE;
    };
    $dialog = new Record('tablename', 'indexfield', array());
    $dialog->setPermissionCallback($callback);
    $this->assertTrue(
      $dialog->checkRecordPermission(Record::ACTION_INSERT, array())
    );
    $this->assertEquals(
      array(Record::ACTION_INSERT, 'tablename', array()),
      $arguments
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::checkRecordPermission
   */
  public function testCheckRecordPermissionExpectingFalse() {
    $arguments = [];
    $callback = function () use (&$arguments) {
      $arguments = func_get_args();
      return FALSE;
    };
    $dialog = new Record('tablename', 'indexfield', array());
    $dialog->setPermissionCallback($callback);
    $this->assertFalse(
      $dialog->checkRecordPermission(Record::ACTION_INSERT, array())
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::checkRecordPermission
   */
  public function testCheckRecordPermissionWithoutCallbackExpectingTrue() {
    $dialog = new Record('tablename', 'indexfield', array());
    $this->assertTrue(
      $dialog->checkRecordPermission(Record::ACTION_INSERT, array())
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::setDatabaseAccess
   * @covers \Papaya\UI\Dialog\Database\Record::getDatabaseAccess
   */
  public function testGetDatabaseAccess() {
    $dialog = new Record('tablename', 'indexfield', array());
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess,
      $dialog->getDatabaseAccess()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Database\Record::getDatabaseAccess
   */
  public function testGetDatabaseAccessImplizitCreate() {
    $dialog = new Record('tablename', 'indexfield', array());
    $databaseAccess = $dialog->getDatabaseAccess();
    $this->assertInstanceOf(
      \Papaya\Database\Access::class, $databaseAccess
    );
  }

  /***************
   * Fixtures
   ***************/

  /**
   * @param array $parameters
   * @param bool $submitted
   * @return Record
   */
  public function getDialogFixture(array $parameters = array(), $submitted = TRUE) {
    $dialog = new Record(
      'tablename',
      'indexfield',
      array(
        'datafield_one' => NULL,
        'datafield_two' => new \Papaya\Filter\Cast('number'),
        'indexfield' => new \Papaya\Filter\IntegerValue(1)
      )
    );
    $dialog->options()->useToken = FALSE;
    $dialog->hiddenFields()->set(
      'indexfield', empty($parameters['indexfield']) ? NULL : $parameters['indexfield']
    );
    if ($submitted) {
      $dialog->data()->merge($parameters);
      $parameters['confirmation'] = $dialog->hiddenFields()->getChecksum();
    }
    $request = $this->getMock(\Papaya\Request::class, array('getMethod', 'getParameters'));
    $request
      ->expects($this->any())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $request
      ->expects($this->any())
      ->method('getParameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters($parameters)));
    $dialog->papaya(
      $this->mockPapaya()->application(array('Request' => $request))
    );
    return $dialog;
  }

  /***************
   * Callbacks
   ***************/

  public function callbackPermissionFailed() {
    return FALSE;
  }
}
