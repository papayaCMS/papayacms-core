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
* Papaya request parser for wrapper calls
*
* current urls: css, css.php, js, js.php
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParserWrapper extends \PapayaRequestParser {

  /**
  * PCRE pattern for thumbnail links
  * @var string
  */
  private $_pattern = '(/
    (?P<group>(css|js))
    (?:\.php)?
  $)Dix';

  /**
   * Parse url and return data
   * @param \PapayaUrl $url
   * @return FALSE|array
   */
  public function parse($url) {
    if (preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = array();
      $result['mode'] = '.theme-wrapper';
      $result['output_mode'] = $matches['group'];
      return $result;
    }
    return FALSE;
  }
}
