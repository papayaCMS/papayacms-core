<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\Connector;
  use Papaya\Database\Schema;

  abstract class AbstractSchema implements Schema {

    /**
     * @var \Papaya\Database\Connector
     */
    protected $_connector;

    public function __construct(Connector $connector) {
      $this->_connector = $connector;
    }

    protected function getIdentifier($name, $prefix = '') {
      if (trim($prefix) !== '') {
        $result = trim($prefix).'_'.trim($name);
      } else {
        $result = trim($name['name']);
      }
      $result = strtolower($result);
      if (!preg_match('(^[a-z\\d_ ]$)D', $result)) {
        throw new \InvalidArgumentException(
          "Invalid identifier name: $result"
        );
      }
      return $result;
    }
  }
}
