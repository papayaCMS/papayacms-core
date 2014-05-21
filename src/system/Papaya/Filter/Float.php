<?php
/**
* Papaya filter class for a flaot numeric
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Filter
* @version $Id: Float.php 39408 2014-02-27 16:00:49Z weinert $
*/

/**
* Papaya filter class for a float numeric
*
* It can be used to validate if a given input is a float numeric with
* or without a sign. Additionally minimum and maximum limits can be set
* for the number.
*
* The filter function will cast the value to float.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterFloat implements PapayaFilter {

  /**
  * Minimum float value
  *
  * @var float
  */
  protected $_min = NULL;

  /**
  * Maximum float value
  *
  * @var float
  */
  protected $_max = NULL;

  /**
  * Construct object and initialize minimum and maximum limits for the float value
  *
  * @param float $min
  * @param float $max
  */
  public function __construct($min = NULL, $max = NULL) {
    if (!is_null($min)) {
      $this->_min = $min;
    }
    if (!is_null($max)) {
      $this->_max = $max;
    }
  }

  /**
  * Check the float input and throw an exception if it does not match the condition.
  *
  * @throws PapayaFilterException
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    if (!is_numeric($value)) {
      throw new PapayaFilterExceptionNotFloat($value);
    }
    if (!is_null($this->_min) && $value < $this->_min) {
      throw new PapayaFilterExceptionRangeMinimum($this->_min, $value);
    }
    if (!is_null($this->_max) && $value > $this->_max) {
      throw new PapayaFilterExceptionRangeMaximum($this->_max, $value);
    }
    return TRUE;
  }

  /**
  * The filter function is used to read a input value if it is valid. The value is always converted
  * into a float numeric before the validation. So only given limits are validated.
  *
  * @param string $value
  * @return float|NULL
  */
  public function filter($value) {
    $value = (float)$value;
    try {
      $this->validate($value);
      return $value;
    } catch (PapayaFilterException $e) {
    }
    return NULL;
  }
}