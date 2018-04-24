<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogDatabaseRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogDatabaseRecord::__construct
  */
  public function testConstructor() {
    $dialog = new PapayaUiDialogDatabaseRecord(
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
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::_getIdentifierValue
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
      PapayaUiDialogDatabaseRecord::ACTION_NONE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      PapayaUiDialogDatabaseRecord::ACTION_INSERT, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::_getIdentifierValue
  */
  public function testExecuteWithoutIdentifierAndColumnCheck() {
    $dialog = new PapayaUiDialogDatabaseRecord(
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
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::_load
  * @covers PapayaUiDialogDatabaseRecord::_getIdentifierValue
  */
  public function testExecuteWithIdentifierLoadsRecord() {
    $dialog = $this->getDialogFixture(
      array('indexfield' => 42), FALSE
    );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('loadRecord'), array(new stdClass)
    );
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
      PapayaUiDialogDatabaseRecord::ACTION_NONE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      PapayaUiDialogDatabaseRecord::ACTION_UPDATE, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::_insert
  * @covers PapayaUiDialogDatabaseRecord::_compileRecord
  */
  public function testExecuteInsertsRecord() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('insertRecord'), array(new stdClass)
    );
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
      PapayaUiDialogDatabaseRecord::ACTION_INSERT, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      PapayaUiDialogDatabaseRecord::ACTION_UPDATE, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::_insert
  * @covers PapayaUiDialogDatabaseRecord::_compileRecord
  */
  public function testExecuteInsertFailed() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('insertRecord'), array(new stdClass)
    );
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
      PapayaUiDialogDatabaseRecord::ACTION_NONE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      PapayaUiDialogDatabaseRecord::ACTION_INSERT, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::getDatabaseAction
  * @covers PapayaUiDialogDatabaseRecord::getDatabaseActionNext
  */
  public function testExecuteNoPermissionForInsert() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => NULL)
    );
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      PapayaUiDialogDatabaseRecord::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      PapayaUiDialogDatabaseRecord::ACTION_INSERT, $dialog->getDatabaseActionNext()
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::_update
  * @covers PapayaUiDialogDatabaseRecord::_compileRecord
  */
  public function testExecuteUpdatesRecord() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => 42)
    );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('updateRecord'), array(new stdClass)
    );
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
      PapayaUiDialogDatabaseRecord::ACTION_UPDATE, '_databaseAction', $dialog
    );
    $this->assertAttributeEquals(
      PapayaUiDialogDatabaseRecord::ACTION_UPDATE, '_databaseActionNext', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::execute
  * @covers PapayaUiDialogDatabaseRecord::getDatabaseAction
  * @covers PapayaUiDialogDatabaseRecord::getDatabaseActionNext
  */
  public function testExecuteNoPermissionForUpdate() {
    $dialog = $this->getDialogFixture(
      array('datafield_one' => 'sample', 'datafield_two' => '23', 'indexfield' => 42)
    );
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertFalse($dialog->execute());
    $this->assertEquals(
      PapayaUiDialogDatabaseRecord::ACTION_NONE, $dialog->getDatabaseAction()
    );
    $this->assertEquals(
      PapayaUiDialogDatabaseRecord::ACTION_UPDATE, $dialog->getDatabaseActionNext()
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::setPermissionCallback
  */
  public function testSetPermissionCallback() {
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $dialog->setPermissionCallback(array($this, 'callbackPermissionFailed'));
    $this->assertAttributeEquals(
      array($this, 'callbackPermissionFailed'), '_callbackPermissions', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::checkRecordPermission
  */
  public function testCheckRecordPermissionExpectingTrue() {
    $arguments = [];
    $callback = function() use (&$arguments) {
      $arguments = func_get_args();
      return TRUE;
    };
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $dialog->setPermissionCallback($callback);
    $this->assertTrue(
      $dialog->checkRecordPermission(PapayaUiDialogDatabaseRecord::ACTION_INSERT, array())
    );
    $this->assertEquals(
      array(PapayaUiDialogDatabaseRecord::ACTION_INSERT, 'tablename', array()),
      $arguments
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::checkRecordPermission
  */
  public function testCheckRecordPermissionExpectingFalse() {
    $arguments = [];
    $callback = function() use (&$arguments) {
      $arguments = func_get_args();
      return FALSE;
    };
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $dialog->setPermissionCallback($callback);
    $this->assertFalse(
      $dialog->checkRecordPermission(PapayaUiDialogDatabaseRecord::ACTION_INSERT, array())
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::checkRecordPermission
  */
  public function testCheckRecordPermissionWithoutCallbackExpectingTrue() {
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $this->assertTrue(
      $dialog->checkRecordPermission(PapayaUiDialogDatabaseRecord::ACTION_INSERT, array())
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::setDatabaseAccess
  */
  public function testSetDatabaseAccess() {
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $databaseAccess = $this->getMock('PapayaDatabaseAccess', array(), array($dialog));
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertAttributeSame(
      $databaseAccess,
      '_databaseAccessObject',
      $dialog
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::getDatabaseAccess
  */
  public function testGetDatabaseAccess() {
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $databaseAccess = $this->getMock('PapayaDatabaseAccess', array(), array($dialog));
    $dialog->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess,
      $dialog->getDatabaseAccess()
    );
  }

  /**
  * @covers PapayaUiDialogDatabaseRecord::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplizitCreate() {
    $dialog = new PapayaUiDialogDatabaseRecord('tablename', 'indexfield', array());
    $databaseAccess = $dialog->getDatabaseAccess();
    $this->assertInstanceOf(
      'PapayaDatabaseAccess', $databaseAccess
    );
  }

  /***************
  * Fixtures
  ***************/

  public function getDialogFixture(array $parameters = array(), $submitted = TRUE) {
    $dialog = new PapayaUiDialogDatabaseRecord(
      'tablename',
      'indexfield',
      array(
        'datafield_one' => NULL,
        'datafield_two' => new PapayaFilterCast('number'),
        'indexfield' => new PapayaFilterInteger(1)
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
    $request = $this->getMock('PapayaRequest', array('getMethod', 'getParameters'));
    $request
      ->expects($this->any())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $request
      ->expects($this->any())
      ->method('getParameters')
      ->will($this->returnValue(new PapayaRequestParameters($parameters)));
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
