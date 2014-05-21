<?php
/**
* Papaya Utiltities - string hyphenation
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
* @version $Id: Hyphenation.php 35308 2011-01-11 16:00:27Z weinert $
*/

/**
* Papaya Utiltities - string hyphenation
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilStringHyphenation {

  /**
  * German hypenation patterns
  * @var array(string=>string)
  */
  private static $_patternsGerman = array(
    // vowel consonant vowel
    '((.*)([aeiou])([bcdghjklmnpqrstvwxyz]|ck|ch)([aeiou])(.*))' => '${1}${2}-${3}${4}${5}',
    // vowel consonant consonant vowel
    '(([aeiou])([bdghjklmnpqrstvwxyz])([bcdghjklmnpqrstvwxyz])([aeiou])(.*))i' =>
      '${1}${2}-${3}${4}${5}',
    '((.*)([aeiou])([bdghjklmnpqrstvwxyz])([bcdghjklmnpqrstvwxyz])([aeiou])(.*))' =>
      '${1}${2}${3}-${4}${5}${6}',
    '((.*)([aeiou])([bdghjklmnpqrstvwxyz])(sch)([aeiou])(.*))' =>
      '${1}${2}${3}-${4}${5}${6}',
    '(zl)' => 'z-l',
    '(ts([a-z]))' => 'ts-$1'
  );


  /**
  * Adds an "-" at each possible hypenation position in a German word.
  *
  * This method is by no means complete but it should help to avoid to lange words.
  *
  * @param string $word
  * @return string
  */
  public static function german($word) {
    return preg_replace(
      array_keys(self::$_patternsGerman), array_values(self::$_patternsGerman), $word
    );
  }
}