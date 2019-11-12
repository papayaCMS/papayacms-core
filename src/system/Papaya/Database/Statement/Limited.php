<?php

namespace Papaya\Database\Statement {

  use Papaya\Database\Connection;
  use Papaya\Database\Statement;

  class Limited
    extends ExecutableStatement {

    /**
     * @var \Papaya\Database\Statement
     */
    private $_original;
    /**
     * @var NULL|int
     */
    private $_limit;
    /**
     * @var NULL|int
     */
    private $_offset;

    public function __construct(
      Connection $databaseConnection,
      Statement $original, $limit = NULL, $offset = NULL
    ) {
      parent::__construct($databaseConnection);
      $this->_original = $original;
      $this->_limit = $limit;
      $this->_offset = $offset;
    }

    /**
     * @param bool $allowPrepared
     * @return string
     */
    public function getSQLString($allowPrepared = TRUE) {
      return $this->_original->getSQLString($allowPrepared).$this->getDatabaseConnection()->syntax()->limit(
        $this->_limit, $this->_offset
      );
    }

    /**
     * @param bool $allowPrepared
     * @return array
     */
    public function getSQLParameters($allowPrepared = TRUE) {
      return $this->_original->getSQLParameters($allowPrepared);
    }
  }
}
