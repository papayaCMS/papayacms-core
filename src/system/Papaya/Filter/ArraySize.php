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
 * Papaya filter class for an array size, non arrays throw an exception
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
   * Construct object and initialize minimum and maximum limits
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
   * @param mixed $value
   * @return true
   */
  public function validate($value) {
    if (!is_array($value)) {
      throw new Filter\Exception\UnexpectedType('array');
    }
    $size = count($value);
    if (NULL !== $this->_minimum && $size < $this->_minimum) {
      throw new Exception\OutOfRange\ToSmall($this->_minimum, $size);
    }
    if (NULL !== $this->_maximum && $size > $this->_maximum) {
      throw new Exception\OutOfRange\ToLarge($this->_maximum, $size);
    }
    return TRUE;
  }

  /**
   * @param mixed $value
   * @return array
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return [];
    }
  }
}
