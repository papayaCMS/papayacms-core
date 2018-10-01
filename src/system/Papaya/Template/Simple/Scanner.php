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
namespace Papaya\Template\Simple;

/**
 * The scanner uses scanner status objects to create a token stream from the input string
 *
 * @package Papaya-Library
 * @subpackage Template
 */
class Scanner {
  /**
   * Scanner status object
   *
   * @var Scanner\Status
   */
  private $_status;

  /**
   * string to parse
   *
   * @var string
   */
  private $_buffer = '';

  /**
   * current offset
   *
   * @var int
   */
  private $_offset = 0;

  /**
   * Constructor, set status object
   *
   * @param Scanner\Status $status
   */
  public function __construct(Scanner\Status $status) {
    $this->_status = $status;
  }

  /**
   * Scan a string for tokens
   *
   * @param array $target token target
   * @param string $string content string
   * @param int $offset start offset
   *
   * @throws \UnexpectedValueException
   *
   * @return int new offset
   */
  public function scan(&$target, $string, $offset = 0) {
    $this->_buffer = $string;
    $this->_offset = $offset;
    while ($token = $this->_next()) {
      $target[] = $token;
      // switch back to previous scanner
      if ($this->_status->isEndToken($token)) {
        return $this->_offset;
      }
      // check for status switch
      if ($newStatus = $this->_status->getNewStatus($token)) {
        // delegate to subscanner
        $this->_offset = $this->_delegate($target, $newStatus);
      }
    }
    if ($this->_offset < \strlen($this->_buffer)) {
      /*
       * @todo a some substring logic for large strings
       */
      throw new \UnexpectedValueException(
        \sprintf(
          'Invalid char "%s" for status "%s" at offset #%d in "%s"',
          \substr($this->_buffer, $this->_offset, 1),
          \get_class($this->_status),
          $this->_offset,
          $this->_buffer
        )
      );
    }
    return $this->_offset;
  }

  /**
   * Get next token
   *
   * @return Scanner\Token|null
   */
  private function _next() {
    if (($token = $this->_status->getToken($this->_buffer, $this->_offset)) &&
      $token->length > 0) {
      $this->_offset += $token->length;
      return $token;
    }
    return NULL;
  }

  /**
   * Got new status, delegate to subscanner.
   *
   * If the status returns a new status object, a new scanner is created to handle it.
   *
   * @param array $target
   * @param Scanner\Status $status
   *
   * @return int offset
   */
  private function _delegate(&$target, $status) {
    $scanner = new self($status);
    return $scanner->scan($target, $this->_buffer, $this->_offset);
  }
}
