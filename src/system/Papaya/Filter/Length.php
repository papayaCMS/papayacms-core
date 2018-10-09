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
use Papaya\Text\UTF8String;
use Papaya\Utility;

/**
 * Papaya filter class for an string length
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
class Length implements Filter {
  /**
   * Minimum limit for integer value
   *
   * @var int
   */
  private $_minimum;

  /**
   * Maximum limit for integer value
   *
   * @var int
   */
  private $_maximum;

  /**
   * @var bool use string as utf-8 and return the code point count
   */
  private $_isUTF8;

  /**
   * Construct object and initialize minimum and maximum limits for the integer value
   *
   * @param int|null $minimum
   * @param int|null $maximum
   * @param bool $isUTF8
   *
   * @throws \RangeException
   */
  public function __construct($minimum = 0, $maximum = NULL, $isUTF8 = FALSE) {
    $this->_minimum = (int)$minimum;
    if (NULL !== $maximum) {
      if ($maximum < $minimum) {
        throw new \RangeException('The maximum needs to be larger then the minimum.');
      }
      $this->_maximum = (int)$maximum;
    }
    $this->_isUTF8 = (bool)$isUTF8;
  }

  /**
   * Check the input and throw an exception if it does not match the condition.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if ($this->_isUTF8) {
      $string = new UTF8String($value, TRUE);
      $length = $string->length();
    } else {
      $length = \strlen($value);
    }
    if (NULL !== $this->_minimum && $length < $this->_minimum) {
      throw new Exception\InvalidLength\ToShort($this->_minimum, $value);
    }
    if (NULL !== $this->_maximum && $length > $this->_maximum) {
      throw new Exception\InvalidLength\ToLong($this->_minimum, $value);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    $value = Utility\Text\UTF8::ensure($value);
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
