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

namespace Papaya\XML;

/**
 * A exception wrapper for libxml errors.
 *
 * This exception provides is created from an libxml error object and provides
 * addition information about the error.
 *
 * @package Papaya-Library
 * @subpackage XML
 */
class Exception extends \Papaya\Exception {
  /**
   * The libxml error
   *
   * @var \libXMLError
   */
  private $_error;

  /**
   * Wrap an libxml error into a php exception
   *
   * @param \libXMLError $error
   */
  public function __construct(\libXMLError $error) {
    parent::__construct(
      \sprintf(
        'Libxml processing error %d at line %d char %d: %s',
        $error->code,
        $error->line,
        $error->column,
        $error->message
      )
    );
    $this->_error = $error;
  }

  /**
   * Return the stored xml error;
   *
   * @return \libXMLError
   */
  public function getError() {
    return $this->_error;
  }

  /**
   * Getter for the libxml error code
   *
   * @return int
   */
  public function getErrorCode() {
    return $this->_error->code;
  }

  /**
   * Getter for the libxml error message
   *
   * @return string
   */
  public function getErrorMessage() {
    return $this->_error->message;
  }

  /**
   * Get the line context of the error
   *
   * This is the line position in the loaded document, not in the php script.
   *
   * @return int
   */
  public function getContextLine() {
    return (int)$this->_error->line;
  }

  /**
   * Get the column context of the error
   *
   * This is the column position in the loaded document, not in the php script.
   *
   * @return int
   */
  public function getContextColumn() {
    return (int)$this->_error->column;
  }

  /**
   * If the document was loaded from a file, the name is returned.
   *
   * If the document was loaded from a string the return value is empty.
   *
   * @return string
   */
  public function getContextFile() {
    return $this->_error->file;
  }
}
