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

use Papaya\Filter;

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
class ArrayElement implements Filter {
  /**
   * elements list
   *
   * @var int
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
   * Check the integer input and throw an exception if it does not match the condition.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if ('' === (string)$value) {
      throw new Exception\IsEmpty();
    }
    if (\is_array($this->_list) && \in_array($value, $this->_list, FALSE)) {
      return TRUE;
    }
    foreach ($this->_list as $element) {
      if ((string)$value === (string)$element) {
        return TRUE;
      }
    }
    throw new Exception\NotIncluded($value);
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param string $value
   *
   * @return mixed|null
   */
  public function filter($value) {
    if (\is_array($this->_list)) {
      $index = \array_search($value, $this->_list, FALSE);
      if (FALSE !== $index) {
        return $this->_list[$index];
      }
    } else {
      foreach ($this->_list as $element) {
        if ((string)$value === (string)$element) {
          return $element;
        }
      }
    }
    return NULL;
  }
}
