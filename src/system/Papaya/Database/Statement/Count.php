<?php

namespace Papaya\Database\Statement {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Statement as DatabaseStatement;

  class Count
    extends ExecutableStatement {

    /**
     * @var DatabaseStatement
     */
    private $_original;

    /**
     * @var NULL|string
     */
    private $_rewriteSQL;


    /**
    * Patterns matching select queries for rewrite (absolute record count)
    * @var string[]
    */
    private static $_sqlSelectPatterns = array(
      '(^\s*(?:SELECT.*?)(\bFROM\b.*)(?:\s+\bORDER\s+BY.*)$)si',
      '(^\s*(?:SELECT.*?)(\bFROM\b.*)$)si'
    );

    public function __construct(
      DatabaseConnection $databaseConnection,
      DatabaseStatement $original
    ) {
      parent::__construct($databaseConnection);
      $this->_original = $original;
    }

    /**
     * @param bool $allowPrepared
     * @return string
     */
    public function getSQLString($allowPrepared = TRUE) {
      if (NULL === $this->_rewriteSQL) {
        $sqlString = $this->_original->getSQLString($allowPrepared);
        foreach (self::$_sqlSelectPatterns as $pattern) {
          if (preg_match($pattern, $sqlString, $match)) {
            return $this->_rewriteSQL = 'SELECT COUNT(*) '.$match[1];
          }
        }
        if (NULL === $this->_rewriteSQL) {
          throw new \BadMethodCallException(
            'Can not rewrite SQL statement to count records.'
          );
        }
      }
      return $this->_rewriteSQL;
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
