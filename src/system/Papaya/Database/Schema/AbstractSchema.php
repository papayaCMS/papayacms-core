<?php

namespace Papaya\Database\Schema {

  use Papaya\Database\Interfaces\Connection;
  use Papaya\Database\Schema;

  abstract class AbstractSchema implements Schema {

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection) {
      $this->connection = $connection;
    }

    protected function getIdentifier($name, $prefix = '') {
      if (trim($prefix) !== '') {
        $result = trim($prefix).'_'.trim($name);
      } else {
        $result = trim($name);
      }
      $result = strtolower($result);
      if (!preg_match('(^[a-z\\d_ ]+$)D', $result)) {
        throw new \InvalidArgumentException(
          "Invalid identifier name: $result"
        );
      }
      return $result;
    }
  }
}
