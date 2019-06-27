<?php

namespace Papaya\Database\Syntax {

  class SQLSource implements Parameter {

    private $_source;

    public function __construct($source) {
      $this->_source = (string)$source;
    }

    public function __toString() {
      return $this->_source;
    }
  }
}
