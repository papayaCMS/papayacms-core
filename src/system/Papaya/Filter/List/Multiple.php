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
* Papaya filter class that validates if all values in a given are in another predefined list
*
* It validates if all of the given array elements are in a given predefined list.
* It is used to validate multiple selects like lists or checkboxes
*
* The filter function will return the element rather then the input.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterListMultiple implements PapayaFilter {

  /**
  * elements list
  * @var integer
  */
  private $_list = NULL;

  /**
  * Construct object and set the list of elements
  *
  * @param array $elements
  */
  public function __construct(array $elements) {
    PapayaUtilConstraints::assertNotEmpty($elements);
    $this->_list = $elements;
  }

  /**
  * Check the integer input and throw an exception if it does not match the condition.
  *
  * @throws PapayaFilterException
  * @param array $value
  * @return TRUE
  */
  public function validate($value) {
    if (!is_array($value)) {
      throw new \PapayaFilterExceptionType('array');
    }
    foreach ($value as $element) {
      if (!in_array($element, $this->_list)) {
        throw new \PapayaFilterExceptionNotEnclosed($element);
      }
    }
    return TRUE;
  }

  /**
  * The filter function is used to read a input value if it is valid.
  *
  * @param array $value
  * @return array
  */
  public function filter($value) {
    $result = array();
    foreach ((array)$value as $element) {
      $index = array_search($element, $this->_list);
      if ($index === 0 || $index > 0) {
        $result[] = $this->_list[$index];
      }
    }
    return $result;
  }
}
