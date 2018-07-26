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

use Papaya\Content\Tables;
use Papaya\Database\Exception\Query;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseAccessTest extends PapayaTestCase {

  /**
  * @covers \PapayaDatabaseAccess::__construct
  */
  public function testConstructor() {
    $owner = new stdClass();
    $access = new \PapayaDatabaseAccess($owner, 'read', 'write');
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
  * @covers \PapayaDatabaseAccess::getDatabaseConnector
  */
  public function testGetDatabaseConnector() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertEquals(
      $connector,
      $access->getDatabaseConnector()
    );
  }

  /**
  * @covers \PapayaDatabaseAccess::getDatabaseConnector
  */
  public function testGetDatabaseConnectorWithoutManagerExistingExpectingNull() {
    $access = new \PapayaDatabaseAccess(new stdClass, 'read', 'write');
    $access->papaya($this->mockPapaya()->application());
    $this->assertNull(
      $access->getDatabaseConnector()
    );
  }

  /**
  * @covers \PapayaDatabaseAccess::getDatabaseConnector
  * @covers \PapayaDatabaseAccess::setDatabaseConnector
  */
  public function testGetDatabaseConnectorAfterSetDatabaseConnector() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $access = new \PapayaDatabaseAccess(new stdClass, 'read', 'write');
    $access->setDatabaseConnector($connector);
    $this->assertEquals(
      $connector,
      $access->getDatabaseConnector()
    );
  }

  /**
   * @covers       \PapayaDatabaseAccess::__call
   * @dataProvider getDelegationMethodData
   * @param string $functionName
   * @param bool $isWriteFunction
   * @param array $arguments
   */
  public function testDelegation($functionName, $isWriteFunction, array $arguments = array()) {
    $owner = new stdClass;
    // set a random id for equal check
    $owner->randomObjectId = mt_rand();
    $connector = $this
      ->getMockBuilder(db_simple::class)
      ->setMethods(array($functionName))
      ->getMockForAbstractClass();
    $delegationCallbackArguments = array_merge(array($owner), $arguments);
    $connector
      ->expects($this->once())
      ->method($functionName)
      ->withAnyParameters()
      ->willReturnCallback(
        function($owner) use ($delegationCallbackArguments) {
          foreach (func_get_args() as $index => $argument) {
            $this->assertEquals(
              $delegationCallbackArguments[$index],
              $argument,
              'Argument #'.$index.' is not the same.'
            );
          }
          return TRUE;
        }
      );
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
  * @covers \PapayaDatabaseAccess::__call
  */
  public function testDelegationWithUpperCaseFunctionName() {
    $owner = new stdClass;
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess($owner, $connector);
    /** @noinspection CallableReferenceNameMismatchInspection */
    $this->assertTrue(
      $access->QUERYFMT('SELECT ... ', array())
    );
  }

  /**
  * @covers \PapayaDatabaseAccess::__call
  * @covers \PapayaDatabaseAccess::_handleDatabaseException
  */
  public function testDelegationWithDatabaseErrorExpectingMessage() {
    $owner = new stdClass;
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackThrowDatabaseError')));
    $access = new \PapayaDatabaseAccess($owner, 'read', 'write');
    $databaseManager = $this->createMock(PapayaDatabaseManager::class);
    $databaseManager
      ->expects($this->atLeastOnce())
      ->method('getConnector')
      ->with($this->equalTo('read'), $this->equalTo('write'))
      ->will($this->returnValue($connector));
    $messageManager = $this->createMock(PapayaMessageManager::class);
    $messageManager
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageLog::class));
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
  * @covers \PapayaDatabaseAccess::__call
  * @covers \PapayaDatabaseAccess::_handleDatabaseException
  */
  public function testDelegationWithDatabaseErrorExpectingMessageOnErrorHandler() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('queryFmt')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'callbackThrowDatabaseError')));
    $databaseManager = $this->createMock(PapayaDatabaseManager::class);
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
    $access = new \PapayaDatabaseAccess(NULL, 'read', 'write');
    $access->errorHandler(function(PapayaDatabaseException $databaseException) {});
    $access->papaya($application);
    $this->assertFalse($access->queryFmt('SELECT ... ', array()));
  }

  /**
  * @covers \PapayaDatabaseAccess::__call
  */
  public function testDelegationInvalidConnector() {
    $owner = new stdClass();
    $connector = new stdClass();
    $access = $this->getFixtureDatabaseAccess($owner, $connector);
    $this->expectException(BadMethodCallException::class);
    $access->query('SQL');
  }

  /**
  * @covers \PapayaDatabaseAccess::__call
  */
  public function testDelegationInvalidFunction() {
    $owner = new stdClass();
    $access = new \PapayaDatabaseAccess($owner, 'read', 'write');
    $this->expectException(BadMethodCallException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $access->invalidMethodName();
  }

  /**
  * @covers \PapayaDatabaseAccess::getTableName
  */
  public function testGetTableName() {
    $tables = $this->createMock(Tables::class);
    $tables
      ->expects($this->once())
      ->method('get')
      ->with('table', TRUE)
      ->will($this->returnValue('papaya_table'));
    $access = new \PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $access->tables($tables);
    $this->assertEquals('papaya_table', $access->getTableName('table'));
  }

  /**
  * @covers \PapayaDatabaseAccess::getTableName
  */
  public function testGetTableNameWithoutPrefix() {
    $tables = $this->createMock(Tables::class);
    $tables
      ->expects($this->once())
      ->method('get')
      ->with('table', FALSE)
      ->will($this->returnValue('table'));
    $access = new \PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $access->tables($tables);
    $this->assertEquals('table', $access->getTableName('table', FALSE));
  }

  /**
  * @covers \PapayaDatabaseAccess::getTimestamp
  */
  public function testGetTimestamp() {
    $access = new \PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $timestamp = $access->getTimestamp();
    $this->assertGreaterThan(0, $timestamp);
    $this->assertLessThanOrEqual(time(), $timestamp);
  }

  /**
  * @covers \PapayaDatabaseAccess::tables
  */
  public function testTablesGetAfterSet() {
    $tables = $this->createMock(Tables::class);
    $access = new \PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $this->assertSame($tables, $access->tables($tables));
  }

  /**
  * @covers \PapayaDatabaseAccess::tables
  */
  public function testTablesImplicitCreate() {
    $access = new \PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $this->assertInstanceOf(Tables::class, $access->tables());
  }

  /**
  * @covers \PapayaDatabaseAccess::masterOnly
  */
  public function testMasterOnlySetForObject() {
    $access = new \PapayaDatabaseAccess(new stdClass(), 'read', 'write');
    $this->assertTrue($access->masterOnly(TRUE));
  }

  /**
  * @covers \PapayaDatabaseAccess::masterOnly
  */
  public function testMasterOnlySetForObjectAndConnection() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->with($this->equalTo(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertTrue($access->masterOnly(TRUE, TRUE));
  }

  /**
  * @covers \PapayaDatabaseAccess::masterOnly
  */
  public function testMasterOnlyReadConnection() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertTrue($access->masterOnly());
  }

  /**
  * @covers \PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyNoContextExpectingTrue() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertTrue($access->readOnly(TRUE));
  }

  /**
  * @covers \PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyNoContextExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertFalse($access->readOnly(TRUE));
  }

  /**
  * @covers \PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlySetObjectContextExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('setDataModified');
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector);
    $this->assertFalse($access->readOnly(FALSE));
  }

  /**
  * @covers \PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyGetObjectContextExpectingTrue() {
    $options = $this->mockPapaya()->options(
      array('PAPAYA_DATABASE_CLUSTER_SWITCH' => 1)
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $access = $this->getFixtureDatabaseAccess(new stdClass, $connector, $options);
    $this->assertTrue($access->readOnly(TRUE));
  }

  /**
  * @covers \PapayaDatabaseAccess::readOnly
  */
  public function testReadOnlyGetConnectionContextExpectingTrue() {
    $options = $this->mockPapaya()->options(
      array('PAPAYA_DATABASE_CLUSTER_SWITCH' => 2)
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
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
  * @covers \PapayaDatabaseAccess::setDataModified
  */
  public function testSetDataModified() {
    /** @var PHPUnit_Framework_MockObject_MockObject|db_simple $connector */
    $connector = $this->createMock(db_simple::class);
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
  * @covers \PapayaDatabaseAccess::errorHandler
  */
  public function testErrorHandlerGetAfterSet() {
    $access = new \PapayaDatabaseAccess(NULL, 'read', 'write');
    $access->errorHandler(array($this, 'callbackStubErrorHandler'));
    $this->assertEquals(array($this, 'callbackStubErrorHandler'), $access->errorHandler());
  }

  /**
  * @covers \PapayaDatabaseAccess::errorHandler
  */
  public function testErrorHandlerRemoveAfterSet() {
    $access = new \PapayaDatabaseAccess(NULL, 'read', 'write');
    $access->errorHandler(array($this, 'callbackStubErrorHandler'));
    $access->errorHandler(FALSE);
    $this->assertNull($access->errorHandler());
  }

  /**
  * @covers \PapayaDatabaseAccess::errorHandler
  */
  public function testErrorHandlerSetExpectingException() {
    $access = new \PapayaDatabaseAccess(NULL, 'read', 'write');
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
   ***********************************/

  /**
   * @param object $owner
   * @param db_simple|object $connector
   * @param \PapayaConfiguration|NULL $options
   * @return \PapayaDatabaseAccess
   */
  public function getFixtureDatabaseAccess($owner, $connector, $options = NULL) {
    $access = new \PapayaDatabaseAccess($owner, 'read', 'write');
    $databaseManager = $this->createMock(PapayaDatabaseManager::class);
    $databaseManager
      ->expects($this->atLeastOnce())
      ->method('getConnector')
      ->with($this->equalTo('read'), $this->equalTo('write'))
      ->will($this->returnValue($connector));
    $objects = array('Database' => $databaseManager);
    if (NULL !== $options) {
      $objects['Options'] = $options;
    }
    $application = $this->mockPapaya()->application($objects);
    $access->papaya($application);
    return $access;
  }

  /************************************
   * Callbacks
   ************************************/

  /**
   * @throws Query
   */
  public function callbackThrowDatabaseError() {
    throw new Query(
      'Simulated Error', 23, NULL, 'SELECT simulation'
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

