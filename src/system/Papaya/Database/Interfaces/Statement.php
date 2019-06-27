<?php

namespace Papaya\Database\Interfaces {

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
