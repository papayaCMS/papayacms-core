<?php

namespace Papaya\Database {

  interface Statement {

    /**
     * @param bool $allowPrepared
     * @return string
     */
    public function getSQLString($allowPrepared = TRUE);

    /**
     * @param bool $allowPrepared
     * @return array
     */
    public function getSQLParameters($allowPrepared = TRUE);

    /**
     * @return string
     */
    public function __toString();

  }
}
