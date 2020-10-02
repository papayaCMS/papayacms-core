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

use Papaya\Utility;

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
class KeyValue implements Filter {
  /**
   * @var Filter|null
   */
  private $_keyFilter;
  /**
   * @var Filter|null
   */
  private $_valueFilter;

  /**
   * Construct object and set the list of elements
   *
   * @param Filter|null $keyFilter
   * @param Filter|null $valueFilter
   */
  public function __construct(Filter $keyFilter = NULL, Filter $valueFilter = NULL) {
    $this->_keyFilter = $keyFilter;
    $this->_valueFilter = $valueFilter;
  }

  /**
   * Check the input and throw an exception if it does not match the condition.
   *
   * @param mixed $value
   *
   * @return true
   * @throws Exception
   *
   */
  public function validate($value) {
    if (!is_array($value)) {
      throw new Exception\UnexpectedType('array');
    }
    $value = $this->filter($value);
    foreach ($value as $key => $subValue) {
      if (isset($this->_keyFilter)) {
        $this->_keyFilter->validate($key);
      }
      if (isset($this->_valueFilter)) {
        $this->_valueFilter->validate($subValue);
      }
    }
    throw new Exception\NotIncluded($value);
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   *
   * @return mixed|null
   */
  public function filter($value) {
    if (!is_array($value)) {
      return NULL;
    }
    $result = [];
    foreach ($value as $key => $subValue) {
      $result[] = [
        'key' => isset($this->_keyFilter) ? $this->_keyFilter->filter($key) : $key,
        'value' => isset($this->_valueFilter) ? $this->_valueFilter->filter($subValue) : $subValue,
      ];
    }
    return $result ?: NULL;
  }
}
