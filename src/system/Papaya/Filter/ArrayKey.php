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

namespace Papaya\Filter;

/**
 * Papaya filter class that validates if given value is in the list of keys
 *
 * It can be used to validate if a given input equals one of the keys of a given
 * list of elements.
 *
 * The filter function will return the element rather then the input.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class ArrayKey implements \Papaya\Filter {
  /**
   * elements list
   *
   * @var array|\Traversable
   */
  private $_list;

  /**
   * Construct object and set the list of elements
   *
   * @param array|\Traversable $elements
   */
  public function __construct($elements) {
    \Papaya\Utility\Constraints::assertArrayOrTraversable($elements);
    $this->_list = $elements;
  }

  /**
   * Check the input and throw an exception if it does not match the condition.
   *
   * @throws \Papaya\Filter\Exception
   * @param mixed $value
   * @return true
   */
  public function validate($value) {
    if (!(\is_string($value) || \is_int($value) || \is_float($value))) {
      throw new Exception\UnexpectedType('integer, float, string');
    }
    if ('' === (string)$value) {
      throw new Exception\IsEmpty();
    }
    if (\is_array($this->_list) && \array_key_exists($value, $this->_list)) {
      return TRUE;
    }
    if ($this->_list instanceof \ArrayAccess && isset($this->_list[(string)$value])) {
      return TRUE;
    }
    foreach ($this->_list as $key => $element) {
      if ((string)$value === (string)$key) {
        return TRUE;
      }
    }
    throw new Exception\NotIncluded($value);
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   * @return int|null
   */
  public function filter($value) {
    if (!(\is_string($value) || \is_int($value) || \is_float($value))) {
      return;
    }
    if (\is_array($this->_list) && !\array_key_exists($value, $this->_list)) {
      return;
    }
    if ($this->_list instanceof \ArrayAccess && !isset($this->_list[$value])) {
      return;
    }
    foreach ($this->_list as $key => $element) {
      if ((string)$value === (string)$key) {
        return $key;
      }
    }
    return;
  }
}
