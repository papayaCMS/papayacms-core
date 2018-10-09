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
class FloatValue implements Filter {
  /**
   * Minimum float value
   *
   * @var float
   */
  protected $_minimum;

  /**
   * Maximum float value
   *
   * @var float
   */
  protected $_maximum;

  /**
   * Construct object and initialize minimum and maximum limits for the float value
   *
   * @param float $minimum
   * @param float $maximum
   */
  public function __construct($minimum = NULL, $maximum = NULL) {
    if (NULL !== $minimum) {
      $this->_minimum = $minimum;
    }
    if (NULL !== $maximum) {
      $this->_maximum = $maximum;
    }
  }

  /**
   * Check the float input and throw an exception if it does not match the condition.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if (!\is_numeric($value)) {
      throw new Exception\NotNumeric($value);
    }
    if (NULL !== $this->_minimum && $value < $this->_minimum) {
      throw new Exception\OutOfRange\ToSmall($this->_minimum, $value);
    }
    if (NULL !== $this->_maximum && $value > $this->_maximum) {
      throw new Exception\OutOfRange\ToLarge($this->_maximum, $value);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid. The value is always converted
   * into a float numeric before the validation. So only given limits are validated.
   *
   * @param mixed $value
   *
   * @return float|null
   */
  public function filter($value) {
    $value = (float)$value;
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
    }
    return NULL;
  }
}
