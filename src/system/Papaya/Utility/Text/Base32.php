<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Utility\Text;
/**
 * Papaya Utilities for encoding/decoding base32
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Base32 {

  private static $_encodingTable = array(
    '00000' => 'a',
    '00001' => 'b',
    '00010' => 'c',
    '00011' => 'd',
    '00100' => 'e',
    '00101' => 'f',
    '00110' => 'g',
    '00111' => 'h',
    '01000' => 'i',
    '01001' => 'j',
    '01010' => 'k',
    '01011' => 'l',
    '01100' => 'm',
    '01101' => 'n',
    '01110' => 'o',
    '01111' => 'p',
    '10000' => 'q',
    '10001' => 'r',
    '10010' => 's',
    '10011' => 't',
    '10100' => 'u',
    '10101' => 'v',
    '10110' => 'w',
    '10111' => 'x',
    '11000' => 'y',
    '11001' => 'z',
    '11010' => '2',
    '11011' => '3',
    '11100' => '4',
    '11101' => '5',
    '11110' => '6',
    '11111' => '7'
  );

  private static $_decodingTable = array(
    'a' => '00000',
    'b' => '00001',
    'c' => '00010',
    'd' => '00011',
    'e' => '00100',
    'f' => '00101',
    'g' => '00110',
    'h' => '00111',
    'i' => '01000',
    'j' => '01001',
    'k' => '01010',
    'l' => '01011',
    'm' => '01100',
    'n' => '01101',
    'o' => '01110',
    'p' => '01111',
    'q' => '10000',
    'r' => '10001',
    's' => '10010',
    't' => '10011',
    'u' => '10100',
    'v' => '10101',
    'w' => '10110',
    'x' => '10111',
    'y' => '11000',
    'z' => '11001',
    '2' => '11010',
    '3' => '11011',
    '4' => '11100',
    '5' => '11101',
    '6' => '11110',
    '7' => '11111'
  );

  /**
   * encode a binary string using base32 encoding
   *
   * @param string $bytes
   * @param bool $padding
   * @return string
   */
  public static function encode($bytes, $padding = FALSE) {
    $bytes = (string)$bytes;
    $result = '';
    $binary = '';
    //get binary encoded string
    $count = strlen($bytes);
    for ($i = 0; $i < $count; ++$i) {
      $binary .= str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
    }
    //pad value to a multiple of 5
    $bytePadding = strlen($binary) % 5;
    if ($bytePadding > 0) {
      $binary = str_pad($binary, strlen($binary) + 5 - $bytePadding, '0', STR_PAD_RIGHT);
    }
    //get the base32 encoded string
    $count = strlen($binary);
    for ($i = 0; $i < $count; $i += 5) {
      $result .= self::$_encodingTable[substr($binary, $i, 5)];
    }
    $paddingLength = strlen($result) % 8;
    if ($padding && $paddingLength > 0) {
      return $result.str_repeat('=', 8 - $paddingLength);
    } else {
      return $result;
    }
  }

  /**
   * decode a base32 encoded binary string
   *
   * @param string $encodedString
   * @throws \OutOfBoundsException
   * @return string
   */
  public static function decode($encodedString) {
    $encodedString = rtrim($encodedString, '=');
    $result = '';
    $binary = '';
    $count = strlen($encodedString);
    if (in_array($count % 8, array(1, 3, 6))) {
      throw new \OutOfBoundsException(
        sprintf(
          'Invalid input string length for %s::%s',
          __CLASS__,
          __METHOD__
        )
      );
    }
    for ($i = 0; $i < $count; $i++) {
      $char = $encodedString[$i];
      if (isset(self::$_decodingTable[$char])) {
        $binary .= self::$_decodingTable[$char];
      } else {
        throw new \OutOfBoundsException(
          sprintf(
            'Invalid char in input string for %s::%s',
            __CLASS__,
            __METHOD__
          )
        );
      }
    }
    $count = strlen($binary);
    if (substr_count(substr($binary, $count - ($count % 8)), 1) > 0) {
      throw new \OutOfBoundsException(
        sprintf(
          'Invalid padding chars in input string for %s::%s',
          __CLASS__,
          __METHOD__
        )
      );
    }
    for ($i = 0; $i <= ($count - 8); $i += 8) {
      $result .= chr(bindec(substr($binary, $i, 8)));
    }
    return $result;
  }
}
