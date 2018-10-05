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
 * Papaya Utilities - identifier normalization into different output formats
 *
 * The function in this class can be used to interpret a given string as an identifier and normalize
 * it into different version.
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Identifier {
  /**
   * Create a underscore separated, uppercase version of the identifier string.
   *
   * @param string $string
   *
   * @return string
   */
  public static function toUnderscoreUpper($string) {
    return \strtoupper(\implode('_', self::toArray($string)));
  }

  /**
   * Create a underscore separated, lowercase version of the identifier string.
   *
   * @param string $string
   *
   * @return string
   */
  public static function toUnderscoreLower($string) {
    return \implode('_', self::toArray($string));
  }

  /**
   * Create a camel case version of the identifier.
   *
   * @param string $string
   * @param bool $firstUpper - first char should be uppercase
   *
   * @return string
   */
  public static function toCamelCase($string, $firstUpper = FALSE) {
    $parts = self::toArray($string);
    $result = $firstUpper ? \ucfirst($parts[0]) : $parts[0];
    $count = \count($parts);
    for ($i = 1; $i < $count; ++$i) {
      if (\preg_match('(^\d)', $parts[$i])) {
        $result .= '_'.$parts[$i];
      } else {
        $result .= \ucfirst($parts[$i]);
      }
    }
    return $result;
  }

  /**
   * Split the identifier string into a list of lower cased parts.
   *
   * @param string $string
   *
   * @return array
   */
  public static function toArray($string) {
    $camelCasePattern = '(
      (?:[a-z][a-z\d]+)|
      (?:[A-Z][a-z\d]+)|
      (?:[_-][a-z\d]+)|
      (?:[A-Z]+(?![a-z\d]))
    )Sx';
    if (\preg_match_all($camelCasePattern, $string, $matches)) {
      $result = [];
      foreach ($matches[0] as $part) {
        $result[] = \strtolower(\ltrim($part, '_-'));
      }
      return $result;
    }
    return [\strtolower($string)];
  }
}
