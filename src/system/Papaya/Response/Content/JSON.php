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
namespace Papaya\Response\Content;

use Papaya\Response;
use Papaya\Utility;

/**
 * Encodes the provided data into a json string
 *
 * @package Papaya-Library
 * @subpackage Response
 */
class JSON implements Response\Content {
  /**
   * string content buffer
   *
   * @var string
   */
  private $_content;

  /**
   * Initialize object from a string
   *
   * @param mixed $data
   * @param int $options
   * @param int $depth
   */
  public function __construct($data, $options = 0, $depth = 512) {
    $this->_content = (string)json_encode($data, $options, $depth);
  }

  /**
   * Return content length for the http header
   *
   * @return int
   */
  public function length() {
    return \strlen($this->_content);
  }

  /**
   * Output string content to standard output
   */
  public function output() {
    echo $this->_content;
  }

  /**
   * Cast object back into a string
   *
   * @return string
   */
  public function __toString() {
    return $this->_content;
  }
}
