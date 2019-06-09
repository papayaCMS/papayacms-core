<?php

namespace Papaya\Database\Syntax {

  use Papaya\Database\Connector;
  use Papaya\Database\Syntax;

  abstract class AbstractSyntax implements Syntax {

    /**
     * @var \Papaya\Database\Connector
     */
    protected $_connector;

    public function __construct(Connector $connector) {
      $this->_connector = $connector;
    }

    public function identifier($name) {
      return new Identifier($name);
    }

    public function placeholder($name = '') {
      return new Placeholder($name);
    }

    protected function getParameter($parameter) {
      if (
        $parameter instanceof Identifier ||
        $parameter instanceof Placeholder
      ) {
        return (string)$parameter;
      }
      if ($parameter === ':?') {
        return ':?';
      }
      return $this->_connector->quoteString($parameter);
    }
  }
}
