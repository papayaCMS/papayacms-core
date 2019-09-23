<?php

namespace Papaya\Database\Syntax {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Syntax as SQLSyntax;

  abstract class AbstractSyntax implements SQLSyntax {

    /**
     * @var DatabaseConnection
     */
    protected $_connection;

    /**
     * @param DatabaseConnection $connector
     */
    public function __construct(DatabaseConnection $connector) {
      $this->_connection = $connector;
    }

    /**
     * @param string $name
     * @return Identifier
     */
    public function identifier($name) {
      return new Identifier($name);
    }

    /**
     * @param string $name
     * @return Placeholder
     */
    public function placeholder($name = '') {
      return new Placeholder($name);
    }

    /**
     * Compile parameter into an SQL string.
     *
     * Quotes identifiers and string literals using the connection.
     *
     * @param SQLSource|string $parameter
     * @return string
     */
    protected function compileParameter($parameter) {
      if ($parameter instanceof Identifier) {
        return $this->_connection->quoteIdentifier($parameter);
      }
      if (
        $parameter instanceof SQLSource
      ) {
        return (string)$parameter;
      }
      if ($parameter === '?') {
        return '?';
      }
      return $this->_connection->quoteString($parameter);
    }

    /**
     * @param string|Parameter $haystack
     * @param string|Parameter $needle
     * @return string
     */
    public function substringCount($haystack, $needle) {
      $replace = $this->replace($haystack, $needle, $this->substring($needle, 2));
      return new SQLSource('('.$this->characterLength($haystack).' - '.$this->characterLength($replace).')');
    }
  }
}
