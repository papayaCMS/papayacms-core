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
 * Simple file response content
 *
 * Additionally to length() and output(), the object supports to be casted to a string.
 *
 * @package Papaya-Library
 * @subpackage Response
 */
class File implements Response\Content {
  /**
   * string content buffer
   *
   * @var string
   */
  private $_filename;

  /**
   * Initialize object from a string
   *
   * @param string $filename
   */
  public function __construct($filename) {
    Utility\Constraints::assertString($filename);
    $this->_filename = $filename;
  }

  public function getFileName(): string {
    return $this->_filename;
  }

  /**
   * Return content length for the http header
   *
   * @return int
   */
  public function length() {
    return \filesize($this->_filename);
  }

  /**
   * Output string content to standard output
   */
  public function output() {
    \readfile($this->_filename);
  }

  /**
   * Cast object back into a string
   *
   * @return string
   */
  public function __toString() {
    return (string)\file_get_contents($this->_filename);
  }
}
