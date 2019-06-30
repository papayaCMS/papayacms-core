<?php

namespace Papaya\Database {

  class ConnectorTest extends \PHPUnit_Framework_TestCase {

    private $_registeredSample = FALSE;

    public function testRegisterConnectionClass() {
      Connector::registerConnectionClass('sample', Connection_TestProxy::class);
      $this->_registeredSample = TRUE;
      $connector = new Connector('sample://./test');
      $connection = $connector->connect();
      $this->assertInstanceOf(Connection_TestProxy::class, $connection);
    }

    public function tearDown() {
      if ($this->_registeredSample) {
        Connector::unregisterConnectionClass('sample');
      }
      parent::tearDown();
    }
  }

  class Connection_TestProxy implements Connection {

    public function prepare($sql) {
    }

    public function execute($statement, $options = self::EMPTY_OPTIONS) {
    }

    public function escapeString($literal) {
    }

    public function quoteString($literal) {
    }

    public function quoteIdentifier($name) {
    }

    public function getTableName($name, $usePrefix = FALSE) {
    }

    public function schema() {
    }

    public function syntax() {
    }

    public function isExtensionAvailable() {
      return TRUE;
    }

    public function connect() {
      return $this;
    }

    public function disconnect() {
    }

    public function insert($tableName, array $values) {
    }

    public function lastInsertId($tableName, $idField) {
    }

    public function registerFunction(
      $name, callable $function
    ) {
    }
  }
}
