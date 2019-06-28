<?php

namespace Papaya\Database {

  class SQLStatement implements \Papaya\Database\Statement {

    /**
     * @var string
     */
    private $_sql;

    /**
     * @var array
     */
    private $_parameters;

    public function __construct($sql, array $parameters = []) {
      $this->_sql = (string)$sql;
      $this->_parameters = $parameters;
    }

    public function getSQLString($allowPrepared = TRUE) {
      return $this->_sql;
    }

    public function getSQLParameters($allowPrepared = TRUE) {
      return $this->_parameters;
    }

    public function __toString() {
      return $this->getSQLString();
    }
  }
}
