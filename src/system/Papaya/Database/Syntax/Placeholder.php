<?php

namespace Papaya\Database\Syntax {

  class Placeholder extends SQLSource {

    public function __construct($name = '') {
      if (empty($name)) {
        parent::__construct('?');
      } else {
        if (!preg_match('(^[a-z\\d_]$)Di', $name)) {
          throw new \InvalidArgumentException(
            "Invalid identifier name: $name"
          );
        }
        parent::__construct(':'.$name);
      }
    }
  }
}
