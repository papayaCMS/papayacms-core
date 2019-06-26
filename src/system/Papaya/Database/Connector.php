<?php

namespace Papaya\Database {

  abstract class Connector {

    const REQUIRE_ABSOLUTE_COUNT = 1;
    const KEEP_PREVIOUS_RESULT = 2;


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

    public function syntax(Syntax $syntax) {

    }

    /**
     * @param string|Statement $statement
     * @param array|null $parameters
     * @param int $options
     * @return Result
     */
    abstract public function execute($statement, array $parameters = NULL, $options = 0);

    abstract public function quoteString($literal);

    abstract public function registerFunction($name, callable $function);
  }
}
