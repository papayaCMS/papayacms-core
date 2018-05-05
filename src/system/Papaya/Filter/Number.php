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
* Papaya filter class for numbers with a specific length, e.g. credit card or account numbers
*
* Unsigned integer numbers without sign that can also start with one or more zeros.
* Optionally, a minimum and/or maximum number of digits can be set.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterNumber implements \PapayaFilter {
  /**
  * Minimum number of digits
  * @var integer
  */
  private $_minimumLength = NULL;

  /**
  * Maximum number of digits
  * @var integer
  */
  private $_maximumLength = NULL;

  /**
  * Constructor
  *
  * @param integer $minimumLength optional, default NULL
  * @param integer $maximumLength optional, default NULL
  * @throws \UnexpectedValueException
  */
  public function __construct($minimumLength = NULL, $maximumLength = NULL) {
    if ($minimumLength !== NULL) {
      if (!is_numeric($minimumLength) || $minimumLength <= 0) {
        throw new \UnexpectedValueException('Minimum length must be greater than 0.');
      }
    }
    if ($maximumLength !== NULL) {
      if (!is_numeric($maximumLength) || $maximumLength <= 0) {
        throw new \UnexpectedValueException('Maximum length must be greater than 0.');
      }
      if ($minimumLength !== NULL && $minimumLength > $maximumLength) {
        throw new \UnexpectedValueException(
          'Maximum length must be greater than or equal to minimum length.'
        );
      }
    }
    $this->_minimumLength = $minimumLength;
    $this->_maximumLength = $maximumLength;
  }

  /**
  * Check a value and throw an exception if it does not match the constraints
  * @param string $value
  * @throws \PapayaFilterExceptionType
  * @throws \PapayaFilterExceptionRangeMinimum
  * @throws \PapayaFilterExceptionRangeMaximum
  * @return boolean
  */
  public function validate($value) {
    if (!preg_match('(^\d+$)', $value)) {
      throw new \PapayaFilterExceptionType('number');
    }
    if ($this->_minimumLength !== NULL && strlen($value) < $this->_minimumLength) {
      throw new \PapayaFilterExceptionRangeMinimum($this->_minimumLength, strlen($value));
    }
    if ($this->_maximumLength !== NULL && strlen($value) > $this->_maximumLength) {
      throw new \PapayaFilterExceptionRangeMaximum($this->_maximumLength, strlen($value));
    }
    return TRUE;
  }

  /**
  * Filter a value
  *
  * @param string $value
  * @return mixed the filtered value or NULL if not valid
  */
  public function filter($value) {
    try {
      $this->validate(trim($value));
    } catch(\PapayaFilterException $e) {
      return NULL;
    }
    return trim($value);
  }
}
