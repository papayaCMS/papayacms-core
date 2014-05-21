<?php
/**
* Papaya Utiltities - identifer normalization into different output formats
*
* @copyright 2009-2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Util
* @version $Id: Identifier.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya Utiltities - identifer normalization into different output formats
*
* The function in this class can be used to intepret a given stirng as an identifer and normalize
* it into different version.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilStringIdentifier {

  /**
  * Create a underscore seperated, uppercase version of the identifier string.
  *
  * @param string $string
  * @return string
  */
  public static function toUnderscoreUpper($string) {
    return strToUpper(implode('_', self::toArray($string)));
  }

  /**
  * Create a underscore seperated, lowercase version of the identifier string.
  *
  * @param string $string
  * @return string
  */
  public static function toUnderscoreLower($string) {
    return implode('_', self::toArray($string));
  }

  /**
   * Create a camel case version of the identifer.
   *
   * @param string $string
   * @param bool $firstUpper - first char should be uppercase
   * @return string
   */
  public static function toCamelCase($string, $firstUpper = FALSE) {
    $parts = self::toArray($string);
    $result = $firstUpper ? ucfirst($parts[0]) : $parts[0];
    $count = count($parts);
    for ($i = 1; $i < $count; ++$i) {
      if (preg_match('(^\d)', $parts[$i])) {
        $result .= '_'.$parts[$i];
      } else {
        $result .= ucfirst($parts[$i]);
      }
    }
    return $result;
  }

  /**
  * Split the identifier string into a list of lowercased parts.
  *
  * @param string $string
  * @return array
  */
  public static function toArray($string) {
    $camelCasePattern = '(
      (?:[a-z][a-z\d]+)|
      (?:[A-Z][a-z\d]+)|
      (?:[_-][a-z\d]+)|
      (?:[A-Z]+(?![a-z\d]))
    )Sx';
    if (preg_match_all($camelCasePattern, $string, $matches)) {
      $result = array();
      foreach ($matches[0] as $part) {
        $result[] = strToLower(ltrim($part, '_-'));
      }
      return $result;
    } else {
      return array(strToLower($string));
    }
  }
}