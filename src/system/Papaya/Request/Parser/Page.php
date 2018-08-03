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

namespace Papaya\Request\Parser;
/**
 * Papaya request parser for page links
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class Page extends \Papaya\Request\Parser {

  /**
   * PCRE pattern for page links
   *
   * @var string
   */
  private $_pattern = '(/
    (?:(?P<page_title>(?:%[\\dA-Fa-f]{2}|[\\sa-zA-Z\\d_-]+)+)\\.) # title
    (?:(?P<category_id>\\d+)\\.)? # category id
    (?:(?P<page_id>\\d+)\\.) # page id
    (?:(?P<language>[a-zA-Z]{2,4})\\.)? # language identifier
    (?:(?P<mode>(?:[a-oq-z]+|p(?!review))[a-z]*)) # output mode
    (?:\\.
      (?P<preview>preview) # preview
      (?:\\.(?P<preview_time>\\d+))? # preview time
    )?
  $)Dix';

  /**
   * Parse url and return data
   *
   * @param \Papaya\URL $url
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
      $result['page_id'] = (int)$matches['page_id'];
      $result['page_title'] = $matches['page_title'];
      if (!empty($matches['language'])) {
        $result['language'] = $matches['language'];
      }
      if (!empty($matches['category_id'])) {
        $result['category_id'] = (int)$matches['category_id'];
      }
      return $result;
    }
    return FALSE;
  }
}
