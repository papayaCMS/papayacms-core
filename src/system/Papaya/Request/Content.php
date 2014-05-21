<?php
/**
 * Papaya Request Content
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
 * @version $Id: Content.php 39762 2014-04-28 09:05:30Z gies $
 */

/**
 * Encapsulation for the raw request content.
 *
 * Depending on the SAPI the input stream is not seekable and can be read only once in some
 * circumstances. So the cotnent is cached in a static field.
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class PapayaRequestContent {

  const STREAM_PHP_INPUT = 'php://input';

  private $_stream = NULL;
  private $_contents = NULL;
  private $_length = NULL;

  /**
   * @param resource|NULL $stream
   * @param int|NULL $length
   */
  public function __construct($stream = NULL, $length = NULL) {
    $this->_stream = $stream;
    $this->_length = $length;
  }

  /**
   * Returns the content of the request (as available in php://input).
   *
   * @return string
   */
  public function get() {
    if (is_null($this->_contents)) {
      $this->_contents = isset($this->_stream)
        ? stream_get_contents($this->_stream)
        : file_get_contents(self::STREAM_PHP_INPUT);
    }
    return $this->_contents;
  }

  /**
   * Returns the number of bytes in the request content.
   *
   * @return int
   */
  public function length() {
    if (isset($this->_length)) {
      return (int)$this->_length;
    } elseif (isset($_SERVER['HTTP_CONTENT_LENGTH'])) {
      return $this->_length = (int)$_SERVER['HTTP_CONTENT_LENGTH'];
    } else {
      return $this->_length = 0;
    }
  }

  /**
   * Allow to cast the content into an string
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->get();
  }
}