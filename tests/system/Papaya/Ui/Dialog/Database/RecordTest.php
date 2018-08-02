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

/** @noinspection PhpDeprecationInspection */
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogDatabaseRecordTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::__construct
  */
  public function testConstructor() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record(
      'tablename', 'indexfield', array('datafield' => NULL)
    );
    $this->assertAttributeEquals(
      'tablename', '_table', $dialog
    );
    $this->assertAttributeEquals(
      'indexfield', '_identifierColumn', $dialog
    );
    $this->assertAttributeEquals(
      array('datafield' => NULL), '_columns', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::_getIdentifierValue
  */
  public function testExecuteWithoutIdentifier() {
    $dialog = $this->getDialogFixture(
      array(), FALSE
    );
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      NULL, $dialog->hiddenFields()->get('indexfield')
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_NONE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::_getIdentifierValue
  */
  public function testExecuteWithoutIdentifierAndColumnCheck() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record(
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
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::_load
  * @covers \Papaya\Ui\Dialog\Database\Record::_getIdentifierValue
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
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_NONE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_UPDATE, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::_insert
  * @covers \Papaya\Ui\Dialog\Database\Record::_compileRecord
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
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_UPDATE, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::_insert
  * @covers \Papaya\Ui\Dialog\Database\Record::_compileRecord
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
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_NONE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::getDatabaseAction
  * @covers \Papaya\Ui\Dialog\Database\Record::getDatabaseActionNext
  */
  public function testExecuteNoPermissionForInsert() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, $dialog->getDatabaseActionNext()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::_update
  * @covers \Papaya\Ui\Dialog\Database\Record::_compileRecord
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
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_UPDATE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_UPDATE, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::execute
  * @covers \Papaya\Ui\Dialog\Database\Record::getDatabaseAction
  * @covers \Papaya\Ui\Dialog\Database\Record::getDatabaseActionNext
  */
  public function testExecuteNoPermissionForUpdate() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => 42)
    );
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      \Papaya\Ui\Dialog\Database\Record::ACTION_UPDATE, $dialog->getDatabaseActionNext()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::setPermissionCallback
  */
  public function testSetPermissionCallback() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertAttributeEquals(
      array($this, 'callbackPermissionFailed'), '_callbackPermissions', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::checkRecordPermission
  */
  public function testCheckRecordPermissionExpectingTrue() {
    $arguments = [];
    $callback = function() use (&$arguments) {
      $arguments = func_get_args();
      return TRUE;
    };
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
    $dialog->setPermissionCallback($callback);
    $this->assertTrue(
      $dialog->checkRecordPermission(\Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, array())
    );
    $this->assertEquals(
      array(\Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, 'tablename', array()),
      $arguments
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::checkRecordPermission
  */
  public function testCheckRecordPermissionExpectingFalse() {
    $arguments = [];
    $callback = function() use (&$arguments) {
      $arguments = func_get_args();
      return FALSE;
    };
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
    $dialog->setPermissionCallback($callback);
    $this->assertFalse(
      $dialog->checkRecordPermission(\Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, array())
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::checkRecordPermission
  */
  public function testCheckRecordPermissionWithoutCallbackExpectingTrue() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
    $this->assertTrue(
      $dialog->checkRecordPermission(\Papaya\Ui\Dialog\Database\Record::ACTION_INSERT, array())
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::setDatabaseAccess
  */
  public function testSetDatabaseAccess() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertAttributeSame(
      $databaseAccess,
      '_databaseAccessObject',
      $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::getDatabaseAccess
  */
  public function testGetDatabaseAccess() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess,
      $dialog->getDatabaseAccess()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Database\Record::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplizitCreate() {
    $dialog = new \Papaya\Ui\Dialog\Database\Record('tablename', 'indexfield', array());
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
   * @return \Papaya\Ui\Dialog\Database\Record
   */
  public function getDialogFixture(array $parameters = array(), $submitted = TRUE) {
    $dialog = new \Papaya\Ui\Dialog\Database\Record(
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
