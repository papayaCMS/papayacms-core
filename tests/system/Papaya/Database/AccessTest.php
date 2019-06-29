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

namespace Papaya\Database;

require_once __DIR__.'/../../../bootstrap.php';

class AccessTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Database\Access::__construct
   */
  public function testConstructor() {
    $owner = new \stdClass();
    $access = new Access($owner, 'read', 'write');
    $this->assertAttributeEquals(
      'read', '_uriRead', $access
    );
    $this->assertAttributeSame(
      'write', '_uriWrite', $access
    );
  }

  /**
   * @covers \Papaya\Database\Access::getDatabaseConnector
   */
  public function testGetDatabaseConnector() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $this->assertEquals(
      $connector,
      $access->getDatabaseConnector()
    );
  }

  /**
   * @covers \Papaya\Database\Access::getDatabaseConnector
   */
  public function testGetDatabaseConnectorWithoutManagerExistingExpectingNull() {
    $access = new Access(new \stdClass, 'read', 'write');
    $access->papaya($this->mockPapaya()->application());
    $this->assertNull(
      $access->getDatabaseConnector()
    );
  }

  /**
   * @covers \Papaya\Database\Access::getDatabaseConnector
   * @covers \Papaya\Database\Access::setDatabaseConnector
   */
  public function testGetDatabaseConnectorAfterSetDatabaseConnector() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $access = new Access(new \stdClass, 'read', 'write');
    $access->setDatabaseConnector($connector);
    $this->assertEquals(
      $connector,
      $access->getDatabaseConnector()
    );
  }

  /**
   * @covers \Papaya\Database\Access::getTableName
   */
  public function testGetTableName() {
    $tables = $this->createMock(\Papaya\Content\Tables::class);
    $tables
      ->expects($this->once())
      ->method('get')
      ->with('table', TRUE)
      ->will($this->returnValue('papaya_table'));
    $access = new Access(new \stdClass(), 'read', 'write');
    $access->tables($tables);
    $this->assertEquals('papaya_table', $access->getTableName('table'));
  }

  /**
   * @covers \Papaya\Database\Access::getTableName
   */
  public function testGetTableNameWithoutPrefix() {
    $tables = $this->createMock(\Papaya\Content\Tables::class);
    $tables
      ->expects($this->once())
      ->method('get')
      ->with('table', FALSE)
      ->will($this->returnValue('table'));
    $access = new Access(new \stdClass(), 'read', 'write');
    $access->tables($tables);
    $this->assertEquals('table', $access->getTableName('table', FALSE));
  }

  /**
   * @covers \Papaya\Database\Access::getTimestamp
   */
  public function testGetTimestamp() {
    $access = new Access(new \stdClass(), 'read', 'write');
    $timestamp = $access->getTimestamp();
    $this->assertGreaterThan(0, $timestamp);
    $this->assertLessThanOrEqual(time(), $timestamp);
  }

  /**
   * @covers \Papaya\Database\Access::tables
   */
  public function testTablesGetAfterSet() {
    $tables = $this->createMock(\Papaya\Content\Tables::class);
    $access = new Access(new \stdClass(), 'read', 'write');
    $this->assertSame($tables, $access->tables($tables));
  }

  /**
   * @covers \Papaya\Database\Access::tables
   */
  public function testTablesImplicitCreate() {
    $access = new Access(new \stdClass(), 'read', 'write');
    $this->assertInstanceOf(\Papaya\Content\Tables::class, $access->tables());
  }

  /**
   * @covers \Papaya\Database\Access::masterOnly
   */
  public function testMasterOnlySetForObject() {
    $access = new Access(new \stdClass(), 'read', 'write');
    $this->assertTrue($access->masterOnly(TRUE));
  }

  /**
   * @covers \Papaya\Database\Access::masterOnly
   */
  public function testMasterOnlySetForObjectAndConnection() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->with($this->equalTo(TRUE));
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $this->assertTrue($access->masterOnly(TRUE, TRUE));
  }

  /**
   * @covers \Papaya\Database\Access::masterOnly
   */
  public function testMasterOnlyReadConnection() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $this->assertTrue($access->masterOnly());
  }

  /**
   * @covers \Papaya\Database\Access::getConnectionMode
   */
  public function testReadOnlyNoContextExpectingTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $this->assertSame(Connector::MODE_READ, $access->getConnectionMode());
  }

  /**
   * @covers \Papaya\Database\Access::getConnectionMode
   */
  public function testReadOnlyNoContextExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(TRUE));
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $this->assertSame(Connector::MODE_WRITE, $access->getConnectionMode());
  }

  /**
   * @covers \Papaya\Database\Access::getConnectionMode
   */
  public function testReadOnlySetObjectContextExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('setDataModified');
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $this->assertSame(Connector::MODE_WRITE, $access->getConnectionMode(Connector::MODE_WRITE));
  }

  /**
   * @covers \Papaya\Database\Access::getConnectionMode
   */
  public function testReadOnlyGetObjectContextExpectingTrue() {
    $options = $this->mockPapaya()->options(
      array('PAPAYA_DATABASE_CLUSTER_SWITCH' => 1)
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector, $options);
    $this->assertSame(Connector::MODE_READ, $access->getConnectionMode());
  }

  /**
   * @covers \Papaya\Database\Access::getConnectionMode
   */
  public function testReadOnlyGetConnectionContextExpectingTrue() {
    $options = $this->mockPapaya()->options(
      array('PAPAYA_DATABASE_CLUSTER_SWITCH' => 2)
    );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('masterOnly')
      ->will($this->returnValue(FALSE));
    $connector
      ->expects($this->once())
      ->method('getConnectionMode')
      ->will($this->returnValue(Connector::MODE_READ));
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector, $options);
    $this->assertSame(Connector::MODE_READ, $access->getConnectionMode());
  }

  /**
   * @covers \Papaya\Database\Access::setDataModified
   */
  public function testSetDataModified() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Connector $connector */
    $connector = $this->createMock(\Papaya\Database\Connector::class);
    $connector
      ->expects($this->once())
      ->method('setDataModified');
    $access = $this->getFixtureDatabaseAccess(new \stdClass, $connector);
    $access->setDataModified();
    $this->assertAttributeSame(
      TRUE, '_dataModified', $access
    );
  }

  /**
   * @covers \Papaya\Database\Access::errorHandler
   */
  public function testErrorHandlerGetAfterSet() {
    $access = new Access(NULL, 'read', 'write');
    $access->errorHandler(array($this, 'callbackStubErrorHandler'));
    $this->assertEquals(array($this, 'callbackStubErrorHandler'), $access->errorHandler());
  }

  /**
   * @covers \Papaya\Database\Access::errorHandler
   */
  public function testErrorHandlerRemoveAfterSet() {
    $access = new Access(NULL, 'read', 'write');
    $access->errorHandler(array($this, 'callbackStubErrorHandler'));
    $access->errorHandler(FALSE);
    $this->assertNull($access->errorHandler());
  }

  /**
   * @covers \Papaya\Database\Access::errorHandler
   */
  public function testErrorHandlerSetExpectingException() {
    $access = new Access(NULL, 'read', 'write');
    try {
      $access->errorHandler('INVALID_METHOD_NAME');
    } catch (\InvalidArgumentException $e) {
      $this->assertEquals(
        'Given error callback is not callable.',
        $e->getMessage()
      );
    }
  }

  public function callbackStubErrorHandler(Exception $exception) {

  }

  /************************************
   * Fixtures
   ***********************************/

  /**
   * @param object $owner
   * @param \Papaya\Database\Connector|object $connector
   * @param \Papaya\Configuration|NULL $options
   * @return Access
   */
  public function getFixtureDatabaseAccess($owner, $connector, $options = NULL) {
    $access = new Access($owner, 'read', 'write');
    $databaseManager = $this->createMock(Manager::class);
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
   * @throws Exception\QueryFailed
   */
  public function callbackThrowDatabaseError() {
    throw new Exception\QueryFailed(
      'Simulated Error', 23, NULL, 'SELECT simulation'
    );
  }
}

