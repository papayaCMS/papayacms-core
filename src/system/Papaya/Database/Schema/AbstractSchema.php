<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\Connection;
  use Papaya\Database\Schema;

  abstract class AbstractSchema implements Schema {

    /**
     * @var Connection
     */
    protected $_connection;

    public function __construct(Connection $connection) {
      $this->_connection = $connection;
    }

    protected function getIdentifier($name, $prefix = '') {
      if (trim($prefix) !== '') {
        $result = trim($prefix).'_'.trim($name);
      } else {
        $result = trim($name);
      }
      $result = strtolower($result);
      if (!preg_match('(^[a-z\\d_ ]+$)D', $result)) {
        throw new \InvalidArgumentException(
          "Invalid identifier name: $result"
        );
      }
      return $result;
    }

    protected function getQuotedIdentifier($name, $prefix = '') {
      return $this->_connection->quoteIdentifier($this->getIdentifier($name, $prefix));
    }

    protected function getQuotedIdentifiers($names, $prefix = '') {
      $result = [];
      foreach ($names as $name) {
        $result[] = $this->_connection->quoteIdentifier($this->getIdentifier($name, $prefix));
      }
      return $result;
    }
  }
}
