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
 * Papaya filter class for an array size, non arrays are zero size
 *
 * The filter function will cast the value to integer.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class ArraySize implements Filter {
  /**
   * Minimum limit
   *
   * @var int
   */
  private $_minimum;

  /**
   * Maximum limit
   *
   * @var int
   */
  private $_maximum;

  /**
   * Construct object and initialize minimum and maximum limits for the integer value
   *
   * @param int|null $minimum
   * @param int|null $maximum
   *
   * @throws \RangeException
   */
  public function __construct($minimum = NULL, $maximum = NULL) {
    $this->_minimum = $minimum;
    if (NULL !== $minimum) {
      if (
        NULL !== $maximum &&
        $maximum < $minimum) {
        throw new \RangeException('The maximum needs to be larger then the minimum.');
      }
      $this->_maximum = $maximum;
    } elseif (NULL !== $maximum) {
      throw new \RangeException('A maximum was given, but minimum was not.');
    }
  }

  /**
   * Check the array input and throw an exception if it does not match the condition.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    $size = \is_array($value) ? \count($value) : 0;
    $value = (int)$value;
    if (NULL !== $this->_minimum && $value < $this->_minimum) {
      throw new Exception\OutOfRange\ToSmall($this->_minimum, $size);
    }
    if (NULL !== $this->_maximum && $value > $this->_maximum) {
      throw new Exception\OutOfRange\ToLarge($this->_maximum, $size);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid. The value is always converted
   * into an integer before the validation. So only given limits are validated.
   *
   * @param mixed $value
   *
   * @return array|null
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
