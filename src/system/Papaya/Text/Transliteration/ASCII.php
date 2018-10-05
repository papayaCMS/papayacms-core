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
namespace Papaya\Text\Transliteration;

use Papaya\Utility;

/**
 * Transliterate a utf8 string into an ascii string.
 *
 * @package Papaya-Library
 * @subpackage String
 */
class ASCII {
  /**
   * @var
   */
  private static $_mapping;

  /**
   * @var string
   */
  private $_language = 'generic';

  /**
   * Transliterate a utf8 string into an ascii string.
   *
   *
   * @param string $string
   * @param string $language
   *
   * @return string
   */
  public function transliterate($string, $language = 'generic') {
    $this->_language = empty($language) ? 'generic' : $language;
    $result = \preg_replace_callback(
      '([\\xC2-\\xDF][\\x80-\\xBF]|
        \\xE0[\\xA0-\\xBF][\\x80-\\xBF]|[\\xE1-\\xEC][\\x80-\\xBF]{2}|
        \\xED[\\x80-\\x9F][\\x80-\\xBF]|[\\xEE-\\xEF][\\x80-\\xBF]{2}|
        \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}|[\\xF1-\\xF3][\\x80-\\xBF]{3}|
        \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2})xS',
      function($match) {
        $codePoint = Utility\Text\UTF8::getCodePoint($match[0]);
        return $this->mapping()->get($codePoint, $this->_language);
      },
      $string
    );
    return $result;
  }

  /**
   * Mapping tables subobject, this is saved statically to improve performance and
   * memory consumption.
   *
   * @param ASCII\Mapping $mapping
   *
   * @return ASCII\Mapping
   */
  public function mapping(ASCII\Mapping $mapping = NULL) {
    if (NULL !== $mapping) {
      self::$_mapping = $mapping;
    } elseif (NULL === self::$_mapping) {
      self::$_mapping = new ASCII\Mapping();
    }
    return self::$_mapping;
  }

  /**
   * The mapping tables are stored statically, so an explicit method is needed to reset them.
   */
  public function resetMapping() {
    self::$_mapping = NULL;
  }
}
