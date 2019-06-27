<?php

namespace Papaya\Database {

  abstract class Connection implements Interfaces\Connection {

    const REQUIRE_ABSOLUTE_COUNT = 1;
    const KEEP_PREVIOUS_RESULT = 2;

    /**
     * @var \Papaya\Database\Source\Name
     */
    private $_dsn;
    /**
     * @var \Papaya\Database\Syntax
     */
    private $_syntax;
    /**
     * @var \Papaya\Database\Schema
     */
    private $_schema;

    public function __construct(Source\Name $dsn, Syntax $syntax = NULL, Schema $schema = NULL) {
      $this->_dsn = $dsn;
      $this->_syntax = $syntax;
      $this->_schema = $schema;
    }

    /**
     * @return \Papaya\Database\Source\Name
     */
    public function getDSN() {
      return $this->_dsn;
    }

    /**
     * @return \Papaya\Database\Schema
     */
    public function schema() {
      return $this->_schema;
    }

    /**
     * @return \Papaya\Database\Syntax
     */
    public function syntax() {
      return $this->_syntax;
    }
  }
}
