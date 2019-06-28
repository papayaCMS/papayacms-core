<?php

namespace Papaya\Database\Syntax {

  use Papaya\Database\Connection;
  use Papaya\Database\Syntax;

  abstract class AbstractSyntax implements Syntax {

    /**
     * @var Connection
     */
    protected $_connection;

    public function __construct(Connection $connector) {
      $this->_connection = $connector;
    }

    public function identifier($name) {
      return new Identifier($name);
    }

    public function placeholder($name = '') {
      return new Placeholder($name);
    }

    protected function getParameter($parameter) {
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
  }
}
