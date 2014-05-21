<?php
/**
* Papaya Response Headers Object
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Response
* @version $Id: Headers.php 39404 2014-02-27 14:55:43Z weinert $
*/

/**
* Papaya Response Headers Object
*
* Handles http headers of a response, takes care of case normalizing the names, creating arrays
* for headers with multiple values.
*
* @package Papaya-Library
* @subpackage Response
*/
class PapayaResponseHeaders implements IteratorAggregate, ArrayAccess, Countable {

  /**
  * Internal headers list
  * @var array
  */
  private $_headers = array();

  /**
  * Allow to iterate the headers.
  *
  * @return ArrayIterator
  */
  public function getIterator() {
    return new ArrayIterator($this->_headers);
  }

  /**
  * Countable Interface: return the number of different headers.
  *
  * @return integer
  */
  public function count() {
    return count($this->_headers);
  }

  /**
  * Set a header value.
  *
  * If the header value is not replaced and already a value exists it will be converted into an
  * array (a list of all values).
  *
  * @param string $header
  * @param mixed $value
  * @param boolean $replace
  */
  public function set($header, $value, $replace = TRUE) {
    $header = $this->_normalize($header);
    $value = (string)$value;
    PapayaUtilConstraints::assertNotEmpty($header);
    if ($replace || !isset($this->_headers[$header])) {
      $this->_headers[$header] = $value;
    } elseif (is_array($this->_headers[$header])) {
      $this->_headers[$header][] = $value;
    } else {
      $this->_headers[$header] = array(
        $this->_headers[$header], $value
      );
    }
  }

  /**
  * Remove a header from the list if it exists.
  *
  * This removes a header completely, even if it had multiple values.
  *
  * @param string $header
  */
  public function remove($header) {
    $header = $this->_normalize($header);
    if (isset($this->_headers[$header])) {
       unset($this->_headers[$header]);
    }
  }

  /**
  * ArrayAccess: Set/Replace a header value.
  *
  * @param string $header
  * @param mixed $value
  */
  public function offsetSet($header, $value) {
    $this->set($header, $value, TRUE);
  }

  /**
  * ArrayAccess: Check if a header is in the list
  *
  * @param string $header
  * @return boolean
  */
  public function offsetExists($header) {
    return isset($this->_headers[$this->_normalize($header)]);
  }

  /**
  * ArrayAccess: Unset/Remove a header.
  *
  * Unlike remove() this will not check if the header exists before trying to unst it.
  *
  * @param string $header
  */
  public function offsetUnset($header) {
    unset($this->_headers[$this->_normalize($header)]);
  }

  /**
  * ArrayAccess: Get header value(s)
  *
  * @param string $header
  * @return mixed
  */
  public function offsetGet($header) {
    $header = $this->_normalize($header);
    return isset($this->_headers[$header]) ? $this->_headers[$header] : NULL;
  }

  /**
  * This method is used by the other methods to make the header name case insensitive.
  *
  * @param string $header
  * @return string
  */
  private function _normalize($header) {
    $parts = explode('-', strtolower($header));
    return implode('-', array_map('ucfirst', $parts));
  }
}