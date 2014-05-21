<?php
/**
* The scanner uses scanner status objects to create a token stream from the input string
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage Template
* @version $Id: Scanner.php 39407 2014-02-27 15:09:25Z weinert $
*/

/**
* The scanner uses scanner status objects to create a token stream from the input string
*
* @package Papaya-Library
* @subpackage Template
*/
class PapayaTemplateSimpleScanner {

  /**
  * Scanner status object
  * @var PapayaTemplateSimpleScannerStatus
  */
  private $_status = NULL;
  /**
  * string to parse
  * @var string
  */
  private $_buffer = '';
  /**
  * current offset
  * @var integer
  */
  private $_offset = 0;

  /**
  * Constructor, set status object
  *
  * @param PapayaTemplateSimpleScannerStatus $status
  */
  public function __construct(PapayaTemplateSimpleScannerStatus $status) {
    $this->_status = $status;
  }

  /**
   * Scan a string for tokens
   *
   * @param array $target token target
   * @param string $string content string
   * @param integer $offset start offset
   * @throws UnexpectedValueException
   * @return integer new offset
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
    if ($this->_offset < strlen($this->_buffer)) {
      /**
      * @todo a some substring logic for large strings
      */
      throw new UnexpectedValueException(
        sprintf(
          'Invalid char "%s" for status "%s" at offset #%d in "%s"',
          substr($this->_buffer, $this->_offset, 1),
          get_class($this->_status),
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
  * @return PapayaTemplateSimpleScannerToken|NULL
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
  * @param PapayaTemplateSimpleScannerStatus $status
  * @return PapayaTemplateSimpleScanner
  */
  private function _delegate(&$target, $status) {
    $scanner = new self($status);
    return $scanner->scan($target, $this->_buffer, $this->_offset);
  }
}