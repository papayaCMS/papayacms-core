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
* Papaya filter class for an integer number
*
* It can be used to validate if a given input is an integer number with
* or without a sign. Additionally minimum and maximum limits can be set
* for the number.
*
* The filter function will cast the value to integer.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterInteger implements \Papaya\Filter {

  /**
  * Minimum limit for integer value
  * @var integer
  */
  private $_minimum = NULL;
  /**
  * Maximum limit for integer value
  * @var integer
  */
  private $_maximum = NULL;

  /**
   * Construct object and initialize minimum and maximum limits for the integer value
   *
   * @param integer|NULL $minimum
   * @param integer|NULL $maximum
   * @throws \RangeException
   */
  public function __construct($minimum = NULL, $maximum = NULL) {
    $this->_minimum = $minimum;
    if (isset($minimum)) {
      if (isset($maximum) &&
          $maximum < $minimum) {
        throw new \RangeException('The maximum needs to be larger then the minimum.');
      }
      $this->_maximum = $maximum;
    } elseif (isset($maximum)) {
      throw new \RangeException('A maximum was given, but minimum was not.');
    }
  }

  /**
  * Check the integer input and throw an exception if it does not match the condition.
  *
  * @throws \PapayaFilterException
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    if (preg_match('(^[+-]?\d+$)D', $value)) {
      $value = (int)$value;
      if (isset($this->_minimum) && $value < $this->_minimum) {
        throw new \Papaya\Filter\Exception\OutOfRange\ToSmall($this->_minimum, $value);
      }
      if (isset($this->_maximum) && $value > $this->_maximum) {
        throw new \Papaya\Filter\Exception\OutOfRange\ToLarge($this->_maximum, $value);
      }
    } else {
      throw new \Papaya\Filter\Exception\UnexpectedType('integer number');
    }
    return TRUE;
  }

  /**
  * The filter function is used to read a input value if it is valid. The value is always converted
  * into an integer before the validation. So only given limits are validated.
  *
  * @param string $value
  * @return integer|NULL
  */
  public function filter($value) {
    $value = (int)$value;
    try {
      $this->validate($value);
      return $value;
    } catch (\PapayaFilterException $e) {
      return NULL;
    }
  }
}
