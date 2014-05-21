<?php
/**
* Papaya request parser for default page links (no page id)
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Request
* @version $Id: Start.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* Papaya request parser for default page links (no page id)
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParserStart extends PapayaRequestParser {

  /**
  * PCRE pattern for thumbnail links
  * @var string
  */
  private $_pattern = '(/
    (?:(?P<page_title>[a-zA-Z\d_-]+)\.) # title
    (?:(?P<language>[a-zA-Z]{2,4})\.)? # language identifier
    (?:(?P<mode>(?:[a-oq-z]+|p(?!review))[a-z]*)) # output mode
    (?:\.
      (?P<preview>preview) # preview
      (?:\.(?P<preview_time>\d+))? # preview time
    )?
  $)Dix';

  /**
   * Parse url and return data
   * @param PapayaUrl $url
   * @return FALSE|array
   */
  public function parse($url) {
    if (preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = array();
      $result['mode'] = 'page';
      $result['output_mode'] = $matches['mode'];
      if (!empty($matches['preview'])) {
        $result['preview'] = TRUE;
        if (isset($matches['preview_time']) &&
            $matches['preview_time'] > 0) {
          $result['preview_time'] = (int)$matches['preview_time'];
        }
      }
      $result['page_title'] = $matches['page_title'];
      if (!empty($matches['language'])) {
        $result['language'] = $matches['language'];
      }
      return $result;
    }
    return FALSE;
  }
}