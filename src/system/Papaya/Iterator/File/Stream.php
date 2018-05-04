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
* This wraps an stream resource into an line iterator.
*
* @package Papaya-Library
* @subpackage Iterator
*/
class PapayaIteratorFileStream implements Iterator {

  const TRIM_NONE = 0;
  const TRIM_RIGHT = 1;

  private $_stream;
  private $_trim;

  private $_line = -1;
  private $_current = FALSE;

  /**
   * create iterator and store stream resource
   *
   * @param resource $stream
   * @param int $trim
   * @throws \InvalidArgumentException
   */
  public function __construct($stream, $trim = self::TRIM_NONE) {
    $this->setStream($stream);
    $this->_trim = $trim;
  }

  /**
   * Close resource if the object is destroyed.
   */
  public function __destruct() {
    if (is_resource($this->_stream)) {
      fclose($this->_stream);
      $this->_stream = NULL;
    }
  }

  /**
   * Store the stream resource, this is a private method, because the resource can only set
   * once - in the constructor.
   *
   * @param resource $stream
   * @throws \InvalidArgumentException
   */
  private function setStream($stream) {
    if (!is_resource($stream)) {
      throw new \InvalidArgumentException('Provided file stream is invalid');
    }
    $this->_stream = $stream;
  }

  /**
  * Return the used stream resource
  *
  * @return resource
  */
  public function getStream() {
    return $this->_stream;
  }

  /**
  * Rewind the stream to the start and read first line
  */
  public function rewind() {
    fseek($this->_stream, 0);
    $this->_line = -1;
    $this->next();
  }

  /**
  * return current line index
  *
  * @return integer
  */
  public function key() {
    return $this->_line;
  }

  /**
  * return current line content
  *
  * @return string|FALSE
  */
  public function current() {
    switch ($this->_trim) {
    case self::TRIM_RIGHT :
      return rtrim($this->_current);
    default :
      return $this->_current;
    }
  }

  /**
  * read next line and increase line index
  */
  public function next() {
    if ($this->_line < 0 || $this->_current !== FALSE) {
      $this->_current = fgets($this->_stream);
      ++$this->_line;
    }
  }

  /**
  * Has an valid line in buffer.
  */
  public function valid() {
    return FALSE !== $this->_current;
  }
}
