<?php

namespace Papaya\Database\Syntax {

  class Identifier implements Parameter {

    private $_name;

    public function __construct($name) {
      if (!preg_match('(^[a-z\\d_ ]$)Di', $name)) {
        throw new \InvalidArgumentException(
          "Invalid identifier name: $name"
        );
      }
      $this->_name = $name;
    }

    public function __toString() {
      return $this->_name;
    }
  }
}
