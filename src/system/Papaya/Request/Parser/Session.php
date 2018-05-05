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
* Papaya request parser for page links
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParserSession extends \PapayaRequestParser {

  /**
  * PCRE pattern for thumbnail links
  * @var string
  */
  private $_pattern = '(
    /(?P<session>sid(?:[a-z]*?)(?:[a-zA-Z\d,-]{20,40}))/
  )Dix';

  /**
   * Parse url and return data
   * @param \PapayaUrl $url
   * @return FALSE|array
   */
  public function parse($url) {
    if (preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = array();
      if (!empty($matches['session'])) {
        $result['session'] = $matches['session'];
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

