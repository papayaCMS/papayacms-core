<?php

namespace Papaya\Database\Connection {

  use Papaya\Database\Interfaces;
  use Papaya\Database\Schema;
  use Papaya\Database\Source;
  use Papaya\Database\Statement\Prepared as PreparedStatement;
  use Papaya\Database\Syntax;

  abstract class AbstractConnection implements \Papaya\Database\Connection {

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

    /**
     * Escape a string for database sql
     *
     * @param mixed $literal Value to escape
     * @return string escaped value.
     */
    public function escapeString($literal) {
      if (is_bool($literal)) {
        return $literal ? 1 : 0;
      }
      if (isset($literal)) {
        return (string)$literal;
      }
      return '';
    }

    /**
     * Eascpae and quote a string for the database sql
     *
     * @param mixed $literal Value to escape
     * @return string escaped value.
     */
    public function quoteString($literal) {
      return "'".$this->escapeString($literal)."'";
    }

    /**
     * @param string $name
     * @return string
     */
    public function quoteIdentifier($name) {
      $result = strtolower(trim($name));
      if (!preg_match('(^[a-z\\d_ ]+$)D', $result)) {
        throw new \InvalidArgumentException(
          "Invalid identifier name: $result"
        );
      }
      return '"'.$result.'"';
    }

    /**
    * Insert records into table
    *
    * @param string $table
    * @param array $values
    * @access public
    * @return boolean
    */
    public function insert($table, array $values) {
      $insert = new \Papaya\Database\Syntax\MassInsert($table, $values);
      return $insert();
    }

    /**
     * @param string $name
     * @param bool $usePrefix
     * @return string
     */
    public function getTableName($name, $usePrefix = FALSE) {
      return $name;
    }

    /**
     * @param string $sql
     * @return PreparedStatement
     */
    public function prepare($sql) {
      return new PreparedStatement($this, $sql);
    }

    /**
     * @param string $name
     * @param callable $function
     */
    public function registerFunction($name, callable $function) {
      throw new \LogicException(
        sprintf('Not implemented: %s::registerFunction()', static::class)
      );
    }
  }
}
