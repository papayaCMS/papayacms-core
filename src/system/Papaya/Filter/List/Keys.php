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
* Papaya filter class that validates if given value is in the list
*
* It can be used to validate if a given input equals one of a given
* list of elements.
*
* The filter function will return the element rather then the input.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterListKeys implements \Papaya\Filter {

  /**
  * elements list
  * @var integer
  */
  private $_list = NULL;

  /**
  * Construct object and set the list of elements
  *
  * @param array|\Traversable $elements
  */
  public function __construct($elements) {
    \PapayaUtilConstraints::assertArrayOrTraversable($elements);
    $this->_list = $elements;
  }

  /**
  * Check the integer input and throw an exception if it does not match the condition.
  *
  * @throws \PapayaFilterException
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    if (!(is_string($value) || is_int($value) || is_float($value))) {
      throw new \PapayaFilterExceptionType('integer, float, string');
    }
    if ((string)$value === '') {
      throw new \Papaya\Filter\Exception\IsEmpty();
    }
    if (is_array($this->_list) && array_key_exists($value, $this->_list)) {
      return TRUE;
    } elseif ($this->_list instanceof \ArrayAccess && isset($this->_list[(string)$value])) {
      return TRUE;
    } else {
      foreach ($this->_list as $key => $element) {
        if ($value == $key) {
          return TRUE;
        }
      }
    }
    throw new \PapayaFilterExceptionNotEnclosed($value);
  }

  /**
  * The filter function is used to read a input value if it is valid.
  *
  * @param string $value
  * @return integer|NULL
  */
  public function filter($value) {
    if (!(is_string($value) || is_int($value) || is_float($value))) {
      return NULL;
    } elseif (is_array($this->_list) && !array_key_exists($value, $this->_list)) {
      return NULL;
    } elseif ($this->_list instanceof \ArrayAccess && !isset($this->_list[$value])) {
      return NULL;
    }
    foreach ($this->_list as $key => $element) {
      if ($value == $key) {
        return $key;
      }
    }
    return NULL;
  }
}
