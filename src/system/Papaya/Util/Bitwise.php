<?php

class PapayaUtilBitwise {

  public static function inBitmask($bit, $bitmask) {
    return ($bitmask & $bit) == $bit;
  }

  /**
   * @param integer ,... $bit
   * @return int
   */
  public static function union() {
    $result = 0;
    foreach (func_get_args() as $bit) {
      $result |= $bit;
    }
    return $result;
  }
}