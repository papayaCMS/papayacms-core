<?php

namespace Papaya\Database\Syntax {

  class Placeholder implements Parameter {

    private $_name;

    public function __construct($name = '') {
      if (empty($name)) {
        $this->_name = ':?';
      } else {
        if (!preg_match('(^[a-z\\d_]$)Di', $name)) {
          throw new \InvalidArgumentException(
            "Invalid identifier name: $name"
          );
        }
        $this->_name = ':'.$name;
      }
    }

    public function __toString() {
      return $this->_name;
    }
  }
}
