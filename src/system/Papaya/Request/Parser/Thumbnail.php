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

use Papaya\Request;

/**
 * Papaya request parser for media database thumbnail links
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class Thumbnail extends Request\Parser {
  /**
   * PCRE pattern for thumbnail links
   *
   * @var string
   */
  private $_pattern = '(/
    (?:[a-zA-Z\d_-]+\.) # title
    (?P<mode>thumb)\. # mode
    (?:(?P<preview>preview)\.)? # is preview
    (?P<media_uri>
      (?P<id>[A-Fa-f\d]{32}) # id
      (?:v(?P<version>\d+))? # version
      _(?P<thumbnail_mode>[a-z]+) # thumbnail mode
      _(?P<thumbnail_size>\d+x\d+) # thumbnail size
      (?:_(?P<thumbnail_params>[A-Fa-f\d]{32}))? # thumbnail parameters
      (?:\.(?P<thumbnail_format>[a-zA-Z\d]+)) # extension
    )
  $)Dix';

  /**
   * Parse url and return data
   *
   * @param \Papaya\URL $url
   *
   * @return false|array
   */
  public function parse($url) {
    if (\preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = [];
      $result['mode'] = 'thumbnail';
      if (!empty($matches['preview'])) {
        $result['preview'] = TRUE;
      }
      $result['media_id'] = $matches['id'];
      $result['media_uri'] = $matches['media_uri'];
      if (!empty($matches['version']) &&
        $matches['version'] > 0) {
        $result['media_version'] = (int)$matches['version'];
      }
      $result['thumbnail_mode'] = $matches['thumbnail_mode'];
      $result['thumbnail_size'] = $matches['thumbnail_size'];
      if (!empty($matches['thumbnail_params'])) {
        $result['thumbnail_params'] = $matches['thumbnail_params'];
      }
      $result['thumbnail_format'] = $matches['thumbnail_format'];
      return $result;
    }
    return FALSE;
  }
}
