<?php

namespace Papaya\Database {

  class SQLStatement implements Interfaces\Statement {

    /**
     * @var string
     */
    private $_sql;

    /**
     * @var array
     */
    private $_parameters;

    public function __construct($sql, array $parameters) {
      $this->_sql = (string)$sql;
      $this->_parameters = $parameters;
    }

    public function getSQLString() {
      return $this->_sql;
    }

    public function getSQLParameters() {
      return $this->_parameters;
    }

    public function __toString() {
      return $this->getSQLString();
    }
  }
}
