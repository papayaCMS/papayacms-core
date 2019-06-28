<?php

namespace Papaya\Database\Statement {

  use Papaya\Database\Connection;
  use Papaya\Database\Statement;

  class Count
    extends ExecutableStatement {

    /**
     * @var \Papaya\Database\Statement
     */
    private $_original;

    /**
     * @var NULL|string
     */
    private $_rewriteSQL;


    /**
    * Patterns matching select queries for rewrite (absolute record count)
    * @var array(string)
    */
    private static $_sqlSelectPatterns = array(
      '(^\s*(?:SELECT.*?)(\bFROM\b.*)(?:\bORDER\s+BY.*)$)si',
      '(^\s*(?:SELECT.*?)(\bFROM\b.*)$)si'
    );

    public function __construct(
      Connection $databaseConnection,
      Statement $original
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
        foreach (self::$_sqlSelectPatterns as $pattern) {
          if (preg_match($pattern, $this->_original->getSQLString($allowPrepared), $match)) {
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
