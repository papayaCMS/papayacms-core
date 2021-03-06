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
 * Papaya filter class that uses validates and array of bits agains a list and converts into
 * a single bitmask value.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Bitmask implements Filter {
  /**
   * List of valid bits
   *
   * @var array(integer)
   */
  protected $_bits = [];

  /**
   * Initialize object and store bit list
   *
   * @param array(integer) $bits
   */
  public function __construct(array $bits) {
    $this->_bits = $bits;
  }

  /**
   * Validate the input value using the function and
   * throw an exception if the validation has failed.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if (empty($value)) {
      return TRUE;
    }
    if (\preg_match('(^[+-]?\d+$)D', $value)) {
      $bits = (int)$value;
      foreach ($this->_bits as $bit) {
        $bits &= ~$bit;
      }
      if (0 === $bits) {
        return TRUE;
      }
      throw new Exception\InvalidValue($value);
    }
    throw new Exception\UnexpectedType('integer number');
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    $value = (int)$value;
    try {
      $this->validate($value);
      return $value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
