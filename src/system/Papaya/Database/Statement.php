<?php

namespace Papaya\Database {

  interface Statement {

    /**
     * @return string
     */
    public function getSQLString();

    /**
     * @return array
     */
    public function getSQLParameters();

    /**
     * @return string
     */
    public function __toString();

  }
}
