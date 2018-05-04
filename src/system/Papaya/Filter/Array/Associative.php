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
 * Papaya filter class for an array with specific elements. It validates the specified elements
 * in the array.
 *
 * The filter function will cast the value to integer.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class PapayaFilterArrayAssociative implements PapayaFilter {

  /**
   * Filters for each array element
   * @var integer
   */
  private $_filters = array();

  /**
   * Construct object and initialize minimum and maximum limits for the integer value
   *
   * @param array $filtersByName
   * @throws \RangeException
   */
  public function __construct(array $filtersByName) {
    $this->_filters = [];
    if (empty($filtersByName)) {
      throw new \InvalidArgumentException('Empty filter definition.');
    }
    foreach ($filtersByName as $name => $filter) {
     if ($filter instanceof \PapayaFilter) {
       $this->_filters[$name] = $filter;
     } else {
       throw new \InvalidArgumentException(
         sprintf('Invalid filter definition for element "%s".', $name)
       );
     }
    }
  }

  /**
   * Check the array elements against each filter.
   *
   * @throws \PapayaFilterException
   * @param mixed $value
   * @return TRUE
   */
  public function validate($value) {
    if (!is_array($value)) {
      throw new \PapayaFilterExceptionType('array');
    }
    foreach ($value as $name => $subValue) {
      if (!isset($this->_filters[$name])) {
        throw new \PapayaFilterExceptionArrayKeyInvalid($name);
      }
      $this->_filters[$name]->validate($subValue);
    }
    return TRUE;
  }

  /**
   * Use the filter for each element. Build a result of the filtered values that are not NULL
   *
   * @param mixed $value
   * @return array|NULL
   */
  public function filter($value) {
    if (!is_array($value)) {
      return NULL;
    }
    $result = [];
    foreach ($value as $name => $subValue) {
      if (isset($this->_filters[$name])) {
        $filtered = $this->_filters[$name]->filter($subValue);
        if (NULL !== $filtered) {
          $result[$name] = $filtered;
        }
      }
    }
    return $result;
  }
}
