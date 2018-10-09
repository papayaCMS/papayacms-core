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
  private static $_blocks = [
    0 => [
      ' _____ ',
      '|  _  |',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|_____|'
    ],
    1 => [
      ' ___',
      '|_  |',
      '  | |',
      '  | |',
      '  | |',
      '  |_|'
    ],
    2 => [
      ' _____',
      '|___  |',
      ' ___| |',
      '|  ___|',
      '| |___',
      '|_____|'
    ],
    3 => [
      ' _____',
      '|___  |',
      '   _| |',
      '  |_  |',
      ' ___| |',
      '|_____|'
    ],
    4 => [
      ' _   _',
      '| | | |',
      '| |_| |',
      '|___  |',
      '    | |',
      '    |_|'
    ],
    5 => [
      ' _____',
      '|  ___|',
      '| |___',
      '|___  |',
      ' ___| |',
      '|_____|'
    ],
    6 => [
      ' _____',
      '|  ___|',
      '| |___',
      '|  _  |',
      '| |_| |',
      '|_____|'
    ],
    7 => [
      ' _____',
      '|___  |',
      '    | |',
      '    | |',
      '    | |',
      '    |_|'
    ],
    8 => [
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _  |',
      '| |_| |',
      '|_____|'
    ],
    9 => [
      ' _____',
      '|  _  |',
      '| |_| |',
      '|___  |',
      ' ___| |',
      '|_____|'
    ],
    'A' => [
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _  |',
      '| | | |',
      '|_| |_|'
    ],
    'B' => [
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _ <',
      '| |_| |',
      '|_____|'
    ],
    'C' => [
      ' _____',
      '|  ___|',
      '| |',
      '| |',
      '| |___',
      '|_____|'
    ],
    'D' => [
      ' ____',
      '|  _ \\',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|____/'
    ],
    'E' => [
      ' _____',
      '|  ___|',
      '| |_',
      '|  _|',
      '| |___',
      '|_____|'
    ],
    'F' => [
      ' _____',
      '|  ___|',
      '| |_',
      '|  _|',
      '| |',
      '|_|'
    ],
    'G' => [
      ' _____',
      '|  ___|',
      '| |  _',
      '| | \ \\',
      '| |_| |',
      '|_____|'
    ],
    'H' => [
      ' _   _',
      '| | | |',
      '| |_| |',
      '|  _  |',
      '| | | |',
      '|_| |_|'
    ],
    'I' => [
      ' _',
      '| |',
      '| |',
      '| |',
      '| |',
      '|_|'
    ],
    'K' => [
      ' _   _',
      '| | / /',
      '| |/ /',
      '|   <',
      '| |\ \\',
      '|_| \_\\'
    ],
    'L' => [
      ' _',
      '| |',
      '| |',
      '| |',
      '| |___',
      '|_____|'
    ],
    'M' => [
      ' __    __',
      '|  \  /  |',
      '|   \/   |',
      '| |\__/| |',
      '| |    | |',
      '|_|    |_|'
    ],
    'N' => [
      ' __    _',
      '|  \  | |',
      '|   \ | |',
      '| |\ \| |',
      '| | \   |',
      '|_|  \__|'
    ],
    'O' => [
      ' _____',
      '|  _  |',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|_____|'
    ],
    'P' => [
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  ___|',
      '| |',
      '|_|'
    ],
    'Q' => [
      ' ______',
      '|  __  |',
      '| |  | |',
      '| | _| |',
      '| |_\  \\',
      '|_______|'
    ],
    'R' => [
      ' _____',
      '|  _  |',
      '| |_| |',
      '|  _ <',
      '| | \ |',
      '|_| |_|'
    ],
    'S' => [
      ' _____',
      '|  ___|',
      '| |___',
      '|___  |',
      ' ___| |',
      '|_____|'
    ],
    'T' => [
      ' _____',
      '|_   _|',
      '  | |',
      '  | |',
      '  | |',
      '  |_|'
    ],
    'U' => [
      ' _   _',
      '| | | |',
      '| | | |',
      '| | | |',
      '| |_| |',
      '|_____|'
    ],
    'V' => [
      ' _   _',
      '| | | |',
      '| | | |',
      '| | | |',
      '\\ \\_/ /',
      ' \\___/'
    ],
    'W' => [
      ' _        _',
      '| |      | |',
      '| |      | |',
      '| |  __  | |',
      '\\ \\_/  \\_/ /',
      ' \\___/\\___/'
    ],
    'X' => [
      ' _   _',
      '| | | |',
      '\\ \\_/ /',
      ' | _ |',
      '/ / \\ \\',
      '|_| |_|'
    ],
    'Y' => [
      ' _   _',
      '| | | |',
      '\\ \\_/ /',
      ' \\   /',
      '  | |',
      '  |_|'
    ],
    'Z' => [
      ' _____',
      '|___  |',
      '   / / ',
      '  / /  ',
      ' / /__ ',
      '|_____|'
    ],
    '-' => [
      '',
      '',
      ' _____',
      '|_____|',
      '',
      ''
    ],
    '+' => [
      '',
      '   _',
      ' _| |_',
      '|_   _|',
      '  |_|',
      ''
    ],
    ':' => [
      '',
      ' _',
      '|_|',
      ' _',
      '|_|',
      ''
    ]
  ];

  /**
   * Return ASCII graphic string
   *
   * @param string $string
   *
   * @return string
   */
  public static function get($string) {
    $string = \strtoupper((string)$string);
    $max = \strlen($string);
    $result = [];
    for ($i = 0; $i < $max; $i++) {
      if (isset(self::$_blocks[$string[$i]])) {
        $char = self::$_blocks[$string[$i]];
        $length = 0;
        foreach ($char as $charStr) {
          if (\strlen($charStr) > $length) {
            $length = \strlen($charStr);
          }
        }
        if (\count($char) > 0) {
          foreach ($char as $line => $charStr) {
            if (empty($result[$line])) {
              $result[$line] = \str_pad(\rtrim($charStr), $length, ' ', STR_PAD_RIGHT);
            } else {
              $result[$line] .= ' '.\str_pad(\rtrim($charStr), $length, ' ', STR_PAD_RIGHT);
            }
          }
        }
      }
    }
    return \implode("\n", $result);
  }
}
