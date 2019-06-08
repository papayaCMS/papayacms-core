<?php

namespace Papaya\Database {

  interface SQLSyntax {

    /**
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public function limit($limit, $offset = 0);
  }
}
