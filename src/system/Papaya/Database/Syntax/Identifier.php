<?php

namespace Papaya\Database\Syntax {

  class Identifier extends SQLSource {

    private $_name;

    public function __construct($name) {
      if (!preg_match('(^[a-z\\d_ ]$)Di', $name)) {
        throw new \InvalidArgumentException(
          "Invalid identifier name: $name"
        );
      }
      parent::__construct($name);
    }
  }
}
