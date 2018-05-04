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

/**
* Papaya request parser for links path and file name
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParserFile extends PapayaRequestParser {

  /**
  * PCRE pattern for thumbnail links
  * @var string
  */
  private $_pattern = '(
    (?:/sid(?:[a-z]*?)(?:[a-zA-Z\d,-]{20,40}))?
    (?P<path>(?:/[^/]+)*/)
    (?P<file>
      (?P<title>[^/?#.]+)
      [^/?#]*?
      (?:\.(?P<extension>([a-oq-z]+|p(?!review))[a-z]*))?
      (\.preview)?
    )?
  $)Dix';

  /**
   * Parse url and return data
   * @param \PapayaUrl $url
   * @return FALSE|array
   */
  public function parse($url) {
    if (preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = array();
      $result['file_path'] = $matches['path'];
      if (!empty($matches['file'])) {
        $result['file_name'] = $matches['file'];
        if (!empty($matches['title'])) {
          $result['file_title'] = $matches['title'];
        }
        if (!empty($matches['extension'])) {
          $result['file_extension'] = $matches['extension'];
        }
      }
      return $result;
    }
    return FALSE;
  }

  /**
  * Allow other parsers after this
  * @return boolean
  */
  public function isLast() {
    return FALSE;
  }
}

