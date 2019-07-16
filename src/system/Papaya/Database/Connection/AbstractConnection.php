<?php

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Schema as DatabaseSchema;
  use Papaya\Database\Source\Name as DataSourceName;
  use Papaya\Database\Statement\Prepared as PreparedStatement;
  use Papaya\Database\Syntax as DatabaseSyntax;
  use Papaya\Database\Syntax\MassInsert;

  abstract class AbstractConnection implements DatabaseConnection {

    /**
     * @var DataSourceName
     */
    private $_dsn;
    /**
     * @var DatabaseSyntax
     */
    private $_syntax;
    /**
     * @var DatabaseSchema
     */
    private $_schema;

    /**
     * @var NULL|DatabaseResult Buffer for the current database result
     */
    private $_buffer;

    public function __construct(
      DataSourceName $dsn, DatabaseSyntax $syntax = NULL, DatabaseSchema $schema = NULL
    ) {
      $this->_dsn = $dsn;
      $this->_syntax = $syntax;
      $this->_schema = $schema;
    }

    /**
     * @return DataSourceName
     */
    public function getDSN() {
      return $this->_dsn;
    }

    /**
     * @return DatabaseSchema
     */
    public function schema() {
      return $this->_schema;
    }

    /**
     * @return DatabaseSyntax
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
     * @param string $quoteChar
     * @return string
     */
    public function quoteIdentifier($name, $quoteChar = '"') {
      $quotedParts = array_map(
        static function ($part) use ($name, $quoteChar) {
          if (!preg_match('(^[a-z\\d_ ]+$)Di', $part)) {
            throw new \InvalidArgumentException(
              "Invalid identifier name: $name"
            );
          }
          return $quoteChar.$part.$quoteChar;
        },
        explode('.', trim($name))
      );
      return implode('.', $quotedParts);
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
      $insert = new MassInsert($this, $table, $values);
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

    protected function cleanup() {
      if (
        $this->_buffer instanceof DatabaseResult
      ) {
        $this->_buffer->free();
        $this->_buffer = FALSE;
      }
    }

    protected function buffer(DatabaseResult $buffer = NULL) {
      if (NULL !== $buffer) {
        $this->_buffer = $buffer;
      }
      return $this->_buffer;
    }
  }
}
