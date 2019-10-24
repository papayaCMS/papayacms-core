<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\Connection {

  use InvalidArgumentException;
  use LogicException;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Database\Source\Name as DataSourceName;
  use Papaya\Database\Schema as DatabaseSchema;
  use Papaya\Database\Syntax as DatabaseSyntax;
  use Papaya\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Connection\AbstractConnection
   */
  class AbstractConnectionTest extends TestCase {

     public function testConstructor() {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);

       $connection = new AbstractConnection_TestProxy($dsn);
       $this->assertSame($dsn, $connection->getDSN());
       $this->assertNull($connection->schema());
       $this->assertNull($connection->syntax());
     }

     public function testConstructorWithSchemaAndSyntax() {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseSchema $schema */
       $schema = $this->createMock(DatabaseSchema::class);
       /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseSyntax $syntax */
       $syntax = $this->createMock(DatabaseSyntax::class);

       $connection = new AbstractConnection_TestProxy($dsn, $syntax, $schema);
       $this->assertSame($dsn, $connection->getDSN());
       $this->assertSame($schema, $connection->schema());
       $this->assertSame($syntax, $connection->syntax());
     }

    /**
     * @param $expected
     * @param $input
     * @testWith
     *   ["foo", "foo"]
     *   ["''foo''", "'foo'"]
     *   ["1", true]
     *   ["0", false]
     *   ["", null]
     */
     public function testEscapeString($expected, $input) {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->assertSame($expected, $connection->escapeString($input));
     }

    /**
     * @param $expected
     * @param $input
     * @testWith
     *   ["'foo'", "foo"]
     *   ["'''foo'''", "'foo'"]
     *   ["'1'", true]
     *   ["'0'", false]
     *   ["''", null]
     */
     public function testQuoteString($expected, $input) {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->assertSame($expected, $connection->quoteString($input));
     }

    /**
     * @param $expected
     * @param $input
     * @param string $quoteChar
     * @testWith
     *   ["\"field\"", "field"]
     *   ["\"table\".\"field\"", "table.field"]
     *   ["\"database\".\"table\".\"foo\"", "database.table.foo"]
     *   ["`database`.`table`.`foo`", "database.table.foo", "`"]
     */
     public function testQuoteIdentifier($expected, $input, $quoteChar = '"') {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->assertSame($expected, $connection->quoteIdentifier($input, $quoteChar));
     }

    /**
     * @param $input
     * @param string $quoteChar
     * @testWith
     *   [""]
     *   ["foo'bar"]
     *   ["foo.'bar"]
     */
     public function testQuoteIdentifierExpectingException($input, $quoteChar = '"') {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->expectException(InvalidArgumentException::class);
       $connection->quoteIdentifier($input, $quoteChar);
     }

     public function testGetTableName() {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->assertSame('table', $connection->getTableName('table'));
     }

     public function testPrepare() {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);
       $statement = $connection->prepare('sql');

       $this->assertInstanceOf(DatabaseStatement\Prepared::class, $statement);
       $this->assertSame('sql', $statement->getSQLString());
       $this->assertSame($connection, $statement->getDatabaseConnection());
     }

     public function testRegisterFunctionExpectingException() {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->expectException(LogicException::class);
       $connection->registerFunction('LOWER', static function() {});
     }

     public function testBufferAndCleanup() {
       /** @var \PHPUnit_Framework_MockObject_MockObject|DataSourceName $dsn */
       $dsn = $this->createMock(DataSourceName::class);
       /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseResult $result */
       $result = $this->createMock(DatabaseResult::class);
       $connection = new AbstractConnection_TestProxy($dsn);

       $this->assertNull($connection->buffer());
       $connection->buffer($result);
       $this->assertSame($result, $connection->buffer());
       $connection->cleanup();
       $this->assertNull($connection->buffer());
       $connection->cleanup();
     }
  }

  class AbstractConnection_TestProxy extends AbstractConnection {

    public function buffer(DatabaseResult $buffer = NULL) {
      return parent::buffer($buffer);
    }

    public function cleanup() {
      parent::cleanup();
    }

    /**
     * @param DatabaseStatement|string $statement
     * @param int $options
     * @return mixed
     */
    public function execute($statement, $options = self::EMPTY_OPTIONS) {
    }

    /**
     * @return bool
     */
    public function isExtensionAvailable() {
      return TRUE;
    }

    /**
     * @return self
     */
    public function connect() {
      return $this;
    }

    public function disconnect() {
    }

    public function lastInsertId($tableName, $idField) {
    }
  }
}
