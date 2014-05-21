<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaDatabaseAccessTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseAccess::__construct
  */
  public function testConstructor() {
    $owner = new stdClass();
    $access = new PapayaDatabaseAccess($owner, 'read', 'write');
    $this->assertAttributeSame(
      $owner, '_owner', $access
    );
    $this->assertAttributeEquals(
      'read', '_uriRead', $access
    );
    $this->assertAttributeSame(
      'write', '_uriWrite', $access
    );
  }

  /**
  * @covers PapayaDatabaseAccess::getDatabaseConnector
  */
  public function testGetDatabaseConnector() {
    $connector = new stdClass();
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertEquals(
      $connector,
      $access->getDatabaseConnector()
    );
  }

  /**
  * @covers PapayaDatabaseAccess::getDatabaseConnector
  */
  public function testGetDatabaseConnectorWithoutManagerExistingExpectingNull() {
    $access = new PapayaDatabaseAccess(new stdClass, 'read', 'write');
    $access->papaya($this->mockPapaya()->application());
    $this->assertNull(
      $access->getDatabaseConnector()
    );
  }

  /**
  * @covers PapayaDatabaseAccess::getDatabaseConnector
  * @covers PapayaDatabaseAccess::setDatabaseConnector
  */
  public function testGetDatabaseConnectorAfterSetDatabaseConnector() {
    $connector = new stdClass();
    $access = new PapayaDatabaseAccess(new stdClass, 'read', 'write');
    $access->setDatabaseConnector($connector);
    $this->assertEquals(
      $connector,
      $access->getDatabaseConnector()
    );
  }

  /**
  * @covers PapayaDatabaseAccess::__call
  * @dataProvider getDelegationMethodData
  */
  public function testDelegation($functionName, $isWriteFunction, $arguments) {
    $owner = new stdClass;
    // set a random id for equal check
    $owner->randomObjectId = rand();
    $connector = $this->getMock('db_simple', array($functionName));
    $this->delegationCallbackArguments = array_merge(
      array($owner),
      $arguments
    );
    $connector
      ->expects($this->once())
      ->method($functionName)
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackDelegationFuntion')));
    $access = $this->getFixtureDatabaseAccess($owner, $connector);
    $this->assertTrue(
      call_user_func_array(array($access, $functionName), $arguments)
    );
    $this->assertAttributeEquals(
      $isWriteFunction,
      '_dataModified',
      $access
    );
  }

  /**
  * @covers PapayaDatabaseAccess::__call
  */
  public function testDelegationWithUpperCaseFunctionName() {
    $owner = new stdClass;
    $connector = $this->getMock('db_simple', array('queryFmt'));
    $connector
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess($owner, $connector);
    $this->assertTrue(
      $access->QUERYFMT('SELECT ... ', array())
    );
  }

  /**
  * @covers PapayaDatabaseAccess::__call
  * @covers PapayaDatabaseAccess::_handleDatabaseException
  */
  public function testDelegationWithDatabaseErrorExpectingMessage() {
    $owner = new stdClass;
    $connector = $this->getMock('db_simple', array('queryFmt'));
    $connector
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackThrowDatabaseError')));
    $access = new PapayaDatabaseAccess($owner, 'read', 'write');
    $databaseManager = $this->getMock('PapayaDatabaseManager', array('getConnector'));
    $databaseManager
      ->expects($this->atLeastOnce())
      ->method('getConnector')
      ->with($this->equalTo('read'), $this->equalTo('write'))
      ->will($this->returnValue($connector));
    $messageManager = $this->getMock('PapayaMessageManager');
    $messageManager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $application = $this->mockPapaya()->application(
      array(
        'Database' => $databaseManager,
        'Messages' => $messageManager
      )
    );
    $access->papaya($application);
    $this->assertFalse($access->queryFmt('SELECT ... ', array()));
  }

  /**
  * @covers PapayaDatabaseAccess::__call
  * @covers PapayaDatabaseAccess::_handleDatabaseException
  */
  public function testDelegationWithDatabaseErrorExpectingMessageOnErrorHandler() {
    $callbackMock = $this->getMock('stdClass', array('errorCallback'));
    $callbackMock
      ->expects($this->once())
      ->method('errorCallback')
      ->with($this->isInstanceOf('PapayaDatabaseException'));
    $connector = $this->getMock('db_simple', array('queryFmt'));
    $connector
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackThrowDatabaseError')));
    $databaseManager = $this->getMock('PapayaDatabaseManager', array('getConnector'));
    $databaseManager
      ->expects($this->atLeastOnce())
      ->method('getConnector')
      ->with($this->equalTo('read'), $this->equalTo('write'))
      ->will($this->returnValue($connector));
    $application = $this->mockPapaya()->application(
      array(
        'Database' => $databaseManager
      )
    );
    $access = new PapayaDatabaseAccess(NULL, 'read', 'write');
    $access->errorHandler(array($callbackMock, 'errorCallback'));
    $access->papaya($application);
    $this->assertFalse($access->queryFmt('SELECT ... ', array()));
  }

  /**
  * @covers PapayaDatabaseAccess::__call
  */
  public function testDelegationInvalidConnector() {
    $owner = new stdClass();
    $connector = new stdClass();
    $access = $this->getFixtureDatabaseAccess($owner, $connector);
    $this->setExpectedException('BadMethodCallException');
    $dummy = $access->query('SQL');
  }

  /**
  * @covers PapayaDatabaseAccess::__call
  */
  public function testDelegationInvalidFunction() {
    $owner = new stdClass();
    $access = new PapayaDatabaseAccess($owner, 'read', 'write');
    $this->setExpectedException('BadMethodCallException');
    $access->invalidMethodName();
  }

  /**
  * @covers PapayaDatabaseAccess::getTableName
  */
  public function testGetTableName() {
    $tables = $this->getMock('PapayaContentTables');
    $tables
      ->expects($this->once())
      ->method('get')
      ->with('table', TRUE)
      ->will($this->returnValue('papaya_table'));
    $access = new PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $access->tables($tables);
    $this->assertEquals('papaya_table', $access->getTableName('table'));
  }

  /**
  * @covers PapayaDatabaseAccess::getTableName
  */
  public function testGetTableNameWithoutPrefix() {
    $tables = $this->getMock('PapayaContentTables');
    $tables
      ->expects($this->once())
      ->method('get')
      ->with('table', FALSE)
      ->will($this->returnValue('table'));
    $access = new PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $access->tables($tables);
    $this->assertEquals('table', $access->getTableName('table', FALSE));
  }

  /**
  * @covers PapayaDatabaseAccess::getTimestamp
  */
  public function testGetTimestamp() {
    $access = new PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $timestamp = $access->getTimestamp();
    $this->assertGreaterThan(0, $timestamp);
    $this->assertLessThanOrEqual(time(), $timestamp);
  }

  /**
  * @covers PapayaDatabaseAccess::tables
  */
  public function testTablesGetAfterSet() {
    $tables = $this->getMock('PapayaContentTables');
    $access = new PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $this->assertSame($tables, $access->tables($tables));
  }

  /**
  * @covers PapayaDatabaseAccess::tables
  */
  public function testTablesImplicitCreate() {
    $access = new PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $this->assertInstanceOf('PapayaContentTables', $access->tables());
  }

  /**
  * @covers PapayaDatabaseAccess::masterOnly
  */
  public function testMasterOnlySetForObject() {
    $access = new PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $this->assertTrue($access->masterOnly(TRUE));
  }

  /**
  * @covers PapayaDatabaseAccess::masterOnly
  */
  public function testMasterOnlySetForObjectAndConnection() {
    $connector = $this->getMock('db_simple', array('masterOnly'));
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->with($this->equalTo(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertTrue($access->masterOnly(TRUE, TRUE));
  }

  /**
  * @covers PapayaDatabaseAccess::masterOnly
  */
  public function testMasterOnlyReadConnection() {
    $connector = $this->getMock('db_simple', array('masterOnly'));
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertTrue($access->masterOnly());
  }

  /**
  * @covers PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyNoContextExpectingTrue() {
    $connector = $this->getMock('db_simple', array('masterOnly'));
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertTrue($access->readOnly(TRUE));
  }

  /**
  * @covers PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyNoContextExpectingFalse() {
    $connector = $this->getMock('db_simple', array('masterOnly'));
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertFalse($access->readOnly(TRUE));
  }

  /**
  * @covers PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlySetObjectContextExpectingFalse() {
    $connector = $this->getMock('db_simple', array('setDataModified'));
    $connector
      ->expects($this->once())
      ->method('setDataModified');
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertFalse($access->readOnly(FALSE));
  }

  /**
  * @covers PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyGetObjectContextExpectingTrue() {
    $options = $this->mockPapaya()->options(
      array('PAPAYA_DATABASE_CLUSTER_SWITCH' => 1)
    );
    $connector = $this->getMock('db_simple', array('masterOnly'));
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector, $options);
    $this->assertTrue($access->readOnly(TRUE));
  }

  /**
  * @covers PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyGetConnectionContextExpectingTrue() {
    $options = $this->mockPapaya()->options(
      array('PAPAYA_DATABASE_CLUSTER_SWITCH' => 2)
    );
    $connector = $this->getMock('db_simple', array('masterOnly', 'readOnly'));
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $connector
      ->expects($this->once())
      ->method('readOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector, $options);
    $this->assertTrue($access->readOnly(TRUE));
  }

  /**
  * @covers PapayaDatabaseAccess::setDataModified
  */
  public function testSetDataModified() {
    $connector = $this->getMock('db_simple', array('setDataModified'));
    $connector
      ->expects($this->once())
      ->method('setDataModified');
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $access->setDataModified();
    $this->assertAttributeSame(
      TRUE, '_dataModified', $access
    );
  }

  /**
  * @covers PapayaDatabaseAccess::errorHandler
  */
  public function testErrorHandlerGetAfterSet() {
    $access = new PapayaDatabaseAccess(NULL, 'read', 'write');
    $access->errorHandler(array($this, 'callbackStubErrorHandler'));
    $this->assertEquals(array($this, 'callbackStubErrorHandler'), $access->errorHandler());
  }

  /**
  * @covers PapayaDatabaseAccess::errorHandler
  */
  public function testErrorHandlerRemoveAfterSet() {
    $access = new PapayaDatabaseAccess(NULL, 'read', 'write');
    $access->errorHandler(array($this, 'callbackStubErrorHandler'));
    $access->errorHandler(FALSE);
    $this->assertNull($access->errorHandler());
  }

  /**
  * @covers PapayaDatabaseAccess::errorHandler
  */
  public function testErrorHandlerSetExpectingException() {
    $access = new PapayaDatabaseAccess(NULL, 'read', 'write');
    try {
      $access->errorHandler('INVALID_METHOD_NAME');
    } catch (InvalidArgumentException $e) {
      $this->assertEquals(
        'Given error callback is not callable.',
        $e->getMessage()
      );
    }
  }

  public function callbackStubErrorHandler(PapayaDatabaseException $exception) {

  }

  /************************************
  * Fixtures
  ************************************/

  public function getFixtureDatabaseAccess($owner, $connector, $options = NULL) {
    $access = new PapayaDatabaseAccess($owner, 'read', 'write');
    $databaseManager = $this->getMock('PapayaDatabaseManager', array('getConnector'));
    $databaseManager
      ->expects($this->atLeastOnce())
      ->method('getConnector')
      ->with($this->equalTo('read'), $this->equalTo('write'))
      ->will($this->returnValue($connector));
    $objects = array('Database' => $databaseManager);
    if (isset($options)) {
      $objects['Options'] = $options;
    }
    $application = $this->mockPapaya()->application($objects);
    $access->papaya($application);
    return $access;
  }

  /************************************
  * Callbacks
  ************************************/

  public function callbackDelegationFuntion($owner) {
    foreach (func_get_args() as $index => $argument) {
      $this->assertEquals(
        $this->delegationCallbackArguments[$index],
        $argument,
        'Argument #'.$index.' is not the same.'
      );
    }
    return TRUE;
  }

  public function callbackThrowDatabaseError() {
    throw new PapayaDatabaseExceptionQuery(
      'Simpultated Error', 23, NULL, 'SELECT simulation'
    );
  }

  /************************************
  * Data Provider
  ************************************/

  public static function getDelegationMethodData() {
    //$functionName, $isWriteFunction, $arguments
    return array(
      'query' => array(
        'query', FALSE, array('SQL', 20, 10, NULL)
      ),
      'queryFmt' => array(
        'queryFmt', FALSE, array('SQL', array(), 20, 10, NULL),
      ),
      'queryFmtWrite' => array(
        'queryFmtWrite', TRUE, array('SQL', array())
      ),
      'queryWrite' => array(
        'queryWrite', TRUE, array('SQL'),
      )
    );
  }
}

