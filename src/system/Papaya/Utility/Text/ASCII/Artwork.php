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

namespace Papaya\Utility\Text\ASCII;
/**
 * Transform text to ASCII-graphic
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Artwork {

  /**
   * Artwork data
   *
   * @var array $_blocks
   */
  private static $_blocks = array(
    0 => array(
      ' _____ ',
      '|  _  |',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|_____|'
    ),
    1 => array(
      ' ___',
      '|_  |',
      '  | |',
      '  | |',
      '  | |',
      '  |_|'
    ),
    2 => array(
      ' _____',
      '|___  |',
      ' ___| |',
      '|  ___|',
      '| |___',
      '|_____|'
    ),
    3 => array(
      ' _____',
      '|___  |',
      '   _| |',
      '  |_  |',
      ' ___| |',
      '|_____|'
    ),
    4 => array(
      ' _   _',
      '| | | |',
      '| |_| |',
      '|___  |',
      '    | |',
      '    |_|'
    ),
    5 => array(
      ' _____',
      '|  ___|',
      '| |___',
      '|___  |',
      ' ___| |',
      '|_____|'
    ),
    6 => array(
      ' _____',
      '|  ___|',
      '| |___',
      '|  _  |',
      '| |_| |',
      '|_____|'
    ),
    7 => array(
      ' _____',
      '|___  |',
      '    | |',
      '    | |',
      '    | |',
      '    |_|'
    ),
    8 => array(
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _  |',
      '| |_| |',
      '|_____|'
    ),
    9 => array(
      ' _____',
      '|  _  |',
      '| |_| |',
      '|___  |',
      ' ___| |',
      '|_____|'
    ),
    'A' => array(
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _  |',
      '| | | |',
      '|_| |_|'
    ),
    'B' => array(
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _ <',
      '| |_| |',
      '|_____|'
    ),
    'C' => array(
      ' _____',
      '|  ___|',
      '| |',
      '| |',
      '| |___',
      '|_____|'
    ),
    'D' => array(
      ' ____',
      '|  _ \\',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|____/'
    ),
    'E' => array(
      ' _____',
      '|  ___|',
      '| |_',
      '|  _|',
      '| |___',
      '|_____|'
    ),
    'F' => array(
      ' _____',
      '|  ___|',
      '| |_',
      '|  _|',
      '| |',
      '|_|'
    ),
    'G' => array(
      ' _____',
      '|  ___|',
      '| |  _',
      '| | \ \\',
      '| |_| |',
      '|_____|'
    ),
    'H' => array(
      ' _   _',
      '| | | |',
      '| |_| |',
      '|  _  |',
      '| | | |',
      '|_| |_|'
    ),
    'I' => array(
      ' _',
      '| |',
      '| |',
      '| |',
      '| |',
      '|_|'
    ),
    'K' => array(
      ' _   _',
      '| | / /',
      '| |/ /',
      '|   <',
      '| |\ \\',
      '|_| \_\\'
    ),
    'L' => array(
      ' _',
      '| |',
      '| |',
      '| |',
      '| |___',
      '|_____|'
    ),
    'M' => array(
      ' __    __',
      '|  \  /  |',
      '|   \/   |',
      '| |\__/| |',
      '| |    | |',
      '|_|    |_|'
    ),
    'N' => array(
      ' __    _',
      '|  \  | |',
      '|   \ | |',
      '| |\ \| |',
      '| | \   |',
      '|_|  \__|'
    ),
    'O' => array(
      ' _____',
      '|  _  |',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|_____|'
    ),
    'P' => array(
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  ___|',
      '| |',
      '|_|'
    ),
    'Q' => array(
      ' ______',
      '|  __  |',
      '| |  | |',
      '| | _| |',
      '| |_\  \\',
      '|_______|'
    ),
    'R' => array(
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _ <',
      '| | \ |',
      '|_| |_|'
    ),
    'S' => array(
      ' _____',
      '|  ___|',
      '| |___',
      '|___  |',
      ' ___| |',
      '|_____|'
    ),
    'T' => array(
      ' _____',
      '|_   _|',
      '  | |',
      '  | |',
      '  | |',
      '  |_|'
    ),
    'U' => array(
      ' _   _',
      '| | | |',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|_____|'
    ),
    'V' => array(
      ' _   _',
      '| | | |',
      '| | | |',
      '| | | |',
      '\\ \\_/ /',
      ' \\___/'
    ),
    'W' => array(
      ' _        _',
      '| |      | |',
      '| |      | |',
      '| |  __  | |',
      '\\ \\_/  \\_/ /',
      ' \\___/\\___/'
    ),
    'X' => array(
      ' _   _',
      '| | | |',
      '\\ \\_/ /',
      ' | _ |',
      '/ / \\ \\',
      '|_| |_|'
    ),
    'Y' => array(
      ' _   _',
      '| | | |',
      '\\ \\_/ /',
      ' \\   /',
      '  | |',
      '  |_|'
    ),
    'Z' => array(
      ' _____',
      '|___  |',
      '   / / ',
      '  / /  ',
      ' / /__ ',
      '|_____|'
    ),
    '-' => array(
      '',
      '',
      ' _____',
      '|_____|',
      '',
      ''
    ),
    '+' => array(
      '',
      '   _',
      ' _| |_',
      '|_   _|',
      '  |_|',
      ''
    ),
    ':' => array(
      '',
      ' _',
      '|_|',
      ' _',
      '|_|',
      ''
    )
  );

  /**
   * Return ASCII graphic string
   *
   * @param string $string
   * @access public
   * @return string
   */
  public static function get($string) {
    $string = strtoupper((string)$string);
    $max = strlen($string);
    $result = array();
    for ($i = 0; $i < $max; $i++) {
      if (isset(self::$_blocks[$string[$i]])) {
        $char = self::$_blocks[$string[$i]];
        $length = 0;
        foreach ($char as $charStr) {
          if (strlen($charStr) > $length) {
            $length = strlen($charStr);
          }
        }
        if (count($char) > 0) {
          foreach ($char as $line => $charStr) {
            if (empty($result[$line])) {
              $result[$line] = str_pad(rtrim($charStr), $length, ' ', STR_PAD_RIGHT);
            } else {
              $result[$line] .= ' '.str_pad(rtrim($charStr), $length, ' ', STR_PAD_RIGHT);
            }
          }
        }
      }
    }
    return implode("\n", $result);
  }
}
