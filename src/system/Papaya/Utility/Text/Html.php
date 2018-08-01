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
 * Papaya Utilities - HTML functions
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Html {

  const PATTERN_HTML_TAG = '(</?[^\s\d\pP>][^>]*>)u';

  private static $_namedEntities;

  /**
   * Escape HTML meta chars in string after stripping tags
   *
   * @param string $string
   * @return string
   */
  public static function escapeStripped($string) {
    return htmlspecialchars(self::stripTags($string), ENT_QUOTES, 'UTF-8');
  }

  /**
   * Strip HTML tags from string (positive match, result needs escaping)
   *
   * @param string $string
   * @return string
   */
  public static function stripTags($string) {
    return preg_replace(self::PATTERN_HTML_TAG, '', $string);
  }

  /**
   * Decode named html entities to utf-8. This will not affect the xml entities, only html like
   * &auml;
   *
   * @param string $string
   * @return string
   */
  public static function decodeNamedEntities($string) {
    // @codeCoverageIgnoreStart
    if (empty(self::$_namedEntities)) {
      self::$_namedEntities = array_flip(
        array_diff(
          get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES, 'UTF-8'),
          get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES, 'UTF-8')
        )
      );
    }
    // @codeCoverageIgnoreEnd
    return strtr($string, self::$_namedEntities);
  }
}
