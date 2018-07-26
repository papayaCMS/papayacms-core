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
 * Papaya filter class for an list of arguments joined by a defined separator character
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Arguments implements \PapayaFilter {

  /**
   * The filters for the arguments
   *
   * @var integer|string
   */
  private $_filters = array();

  /**
   * Separator character
   *
   * @var integer
   */
  private $_separator = ',';

  /**
   * Construct object and initialize pattern and submatch identifier (for filter result)
   *
   * The submatch identifier can be a string (named subpattern) or an integer (index)
   *
   * @param array $argumentFilters
   * @param string $separator
   */
  public function __construct(array $argumentFilters, $separator = ',') {
    $this->_filters = array_values($argumentFilters);
    $this->_separator = $separator;
  }

  /**
   * Check the input value and throw an exception if it does not match the condition.
   *
   * @throws \PapayaFilterException
   * @param string $value
   * @return TRUE
   */
  public function validate($value) {
    if (empty($value)) {
      throw new \PapayaFilterExceptionEmpty();
    }
    $value = explode($this->_separator, $value);
    if (count($value) > count($this->_filters)) {
      throw new \PapayaFilterExceptionCountMismatch(count($this->_filters), count($value), 'array');
    }
    /** @var \PapayaFilter $filter */
    foreach ($this->_filters as $index => $filter) {
      $filter->validate(isset($value[$index]) ? $value[$index] : '');
    }
    return TRUE;
  }

  /**
   * The filter function is used to read an input value if it is valid.
   *
   * If a submatch identifier is available, it returns the submatch.
   *
   * @param string $value
   * @return string|NULL
   */
  public function filter($value) {
    try {
      $this->validate($value);
      $value = explode($this->_separator, $value);
      $result = array();
      /** @var \PapayaFilter $filter */
      foreach ($this->_filters as $index => $filter) {
        $result[] = $filter->filter(isset($value[$index]) ? $value[$index] : '');
      }
      return implode($this->_separator, $result);
    } catch (\PapayaFilterException $e) {
      return NULL;
    }
  }
}
