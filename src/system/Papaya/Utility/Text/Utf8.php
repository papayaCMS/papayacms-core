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
 * Papaya Utilities for UTF-8 strings
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Utf8 {

  const EXT_UNKNOWN = 0;
  const EXT_PCRE = -1;
  const EXT_INTL = 1;
  const EXT_MBSTRING = 2;

  /**
   * The used unicode extension is chached
   *
   * @var integer
   */
  private static $extension = 0;

  /**
   * Checks a UTF-8 string for invalid bytes and converts it to UTF-8.
   *
   * It assumes that the invalid bytes are ISO-8859-1. Valid UTF-8 chars stay unchanged.
   *
   * @param $string
   * @internal param string $str
   * @access public
   * @return string
   */
  public static function ensure($string) {
    $pattern = '(
     (
      [\\xC2-\\xDF][\\x80-\\xBF]| #utf8-2
      \\xE0[\\xA0-\\xBF][\\x80-\\xBF]| #utf8-3
      [\\xE1-\\xEC][\\x80-\\xBF]{2}|
      \\xED[\\x80-\\x9F][\\x80-\\xBF]|
      [\\xEE-\\xEF][\\x80-\\xBF]{2}|
      \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}| #utf8-4
      [\\xF1-\\xF3][\\x80-\\xBF]{3}|
      \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2}
     )
     |([^\\x00-\\x7F]) # latin 1 upper
    )x';
    return preg_replace_callback(
      $pattern,
      function ($charMatch) {
        if (isset($charMatch[2]) && '' !== $charMatch[2]) {
          $c = ord($charMatch[2]);
          return chr(0xC0 | $c >> 6).chr(0x80 | $c & 0x3F);
        }
        return $charMatch[1];
      },
      (string)$string
    );
  }

  /**
   * Return a codepoint for a given utf 8 encoded character
   *
   * @param string $character
   * @return integer|FALSE
   */
  public static function getCodepoint($character) {
    $cp = array(
      0, 0, 0, 0
    );
    $length = strlen($character);
    if ($length > 4) {
      return FALSE;
    }
    for ($i = $length - 1; $i >= 0; --$i) {
      $cp[$i] = ord($character[$i]);
    }
    //single byte utf-8
    if ($cp[0] >= 0 && $cp[0] <= 127) {
      return $cp[0];
    }
    // 2 bytes
    if ($cp[0] >= 192 && $cp[0] <= 223) {
      return ($cp[0] - 192) * 64 + ($cp[1] - 128);
    }
    // 3 bytes
    if ($cp[0] >= 224 && $cp[0] <= 239) {
      return ($cp[0] - 224) * 4096 + ($cp[1] - 128) * 64 + ($cp[2] - 128);
    }
    // 4 bytes
    if ($cp[0] >= 240 && $cp[0] <= 247) {
      return ($cp[0] - 240) * 262144 + ($cp[1] - 128) * 4096 + ($cp[2] - 128) * 64 + ($cp[3] - 128);
    }
    return FALSE;
  }

  /**
   * Get string length of an utf-8 string (works only on utf-8 strings)
   *
   * @param string $string
   * @return integer
   */
  public static function length($string) {
    switch (self::getExtension()) {
      case self::EXT_INTL :
        return grapheme_strlen($string);
      case self::EXT_MBSTRING :
        return mb_strlen($string, 'utf-8');
    }
    $string = preg_replace('(.)su', '.', $string);
    return strlen($string);
  }

  /**
   * Copy a part of an utf-8 string (works only on utf-8 strings)
   *
   * @param string $string
   * @param integer $start
   * @param NULL|integer $length
   * @return string
   */
  public static function copy($string, $start, $length = NULL) {
    switch (self::getExtension()) {
      case self::EXT_INTL :
        if (is_null($length)) {
          return grapheme_substr($string, $start);
        } elseif ($length > 0) {
          if ($start >= 0) {
            $possibleLength = self::length($string) - $start;
          } else {
            $possibleLength = abs($start);
          }
          if ($possibleLength < $length) {
            $length = $possibleLength;
          }
        }
        return grapheme_substr($string, $start, $length);
      case self::EXT_MBSTRING :
        if (is_null($length)) {
          $length = self::length($string);
        }
        if ($length == 0) {
          return '';
        }
        return mb_substr($string, $start, $length, 'utf-8');
    }
    $stringLength = self::length($string);
    if ($start < 0) {
      $start = $stringLength + $start;
    }
    if (is_null($length)) {
      $length = $stringLength;
    } elseif ($length < 0) {
      $length = $stringLength + $length - $start;
    }
    if ($length <= 0) {
      return '';
    }
    $pattern = '(.{'.((int)$start).'}(.{1,'.((int)$length).'}))su';
    if (preg_match($pattern, $string, $match)) {
      return $match[1];
    }
    return '';
  }

  /**
   * Get the position of a substring in an utf-8 string (works only on utf-8 strings)
   *
   * @param string $haystack
   * @param string $needle
   * @param integer $offset
   * @return FALSE|integer
   */
  public static function position($haystack, $needle, $offset = 0) {
    switch (self::getExtension()) {
      case self::EXT_INTL :
        return grapheme_strpos($haystack, $needle, $offset);
      case self::EXT_MBSTRING :
        return mb_strpos($haystack, $needle, $offset, 'utf-8');
    }
    if (FALSE !== ($position = strpos($haystack, $needle, $offset))) {
      return self::length(substr($haystack, 0, $position));
    }
    return FALSE;
  }

  public static function toLowerCase($string) {
    switch (self::getExtension()) {
      case self::EXT_INTL :
        if (class_exists('Transliterator', FALSE)) {
          return \Transliterator::create('Any-Lower')->transliterate($string);
        } elseif (extension_loaded('mbstring')) {
          return mb_strtolower($string, 'utf-8');
        }
      break;
      case self::EXT_MBSTRING :
        return mb_strtolower($string, 'utf-8');
    }
    return preg_replace_callback(
      '([A-Z]+)u',
      function ($match) {
        return strtolower($match[0]);
      },
      $string
    );
  }

  public static function toUpperCase($string) {
    switch (self::getExtension()) {
      case self::EXT_INTL :
        if (class_exists('Transliterator', FALSE)) {
          return \T\ransliterator::create('Any-Upper')->transliterate($string);
        } elseif (extension_loaded('mbstring')) {
          return mb_strtoupper($string, 'utf-8');
        }
      break;
      case self::EXT_MBSTRING :
        return mb_strtoupper($string, 'utf-8');
    }
    return preg_replace_callback(
      '([a-z]+)u',
      function ($match) {
        return strtoupper($match[0]);
      },
      $string
    );
  }

  /**
   * Determine which extension is available and should be used for utf-8 operations.
   * Preference is ext/intl, ext/mbstring and fallback
   *
   * @return integer
   */
  public static function getExtension() {
    if (self::$extension == self::EXT_UNKNOWN) {
      self::$extension = self::EXT_PCRE;
      $extensions = array(
        self::EXT_INTL => 'intl',
        self::EXT_MBSTRING => 'mbstring'
      );
      foreach ($extensions as $extension => $name) {
        if (extension_loaded($name)) {
          self::$extension = $extension;
          break;
        }
      }
    }
    return self::$extension;
  }

  /**
   * Define the extension that should be used for utf-8 operations
   *
   * @param integer $extension
   */
  public static function setExtension($extension) {
    self::$extension = (int)$extension;
  }
}
