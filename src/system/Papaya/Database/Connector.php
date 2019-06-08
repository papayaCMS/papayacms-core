<?php

namespace Papaya\Database {

  abstract class Connector {

    /**
     * @var \Papaya\Database\Source\Name
     */
    private $_dsn;

    public function __construct(Source\Name $dsn) {
      $this->_dsn = $dsn;
    }

    /**
     * @return \Papaya\Database\Source\Name
     */
    public function getDSN() {
      return $this->_dsn;
    }

    public function schema(Schema $schema = NULL) {

    }

    public function syntax(SQLSyntax $syntax) {

    }

    /**
     * @param string|Statement $statement
     * @param array|null $parameters
     * @return Result
     */
    abstract public function execute($statement, array $parameters = NULL);
  }
}
