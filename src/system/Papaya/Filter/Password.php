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
 * Papaya filter class for a password
 *
 * This class is used to validate/filter a password input.
 * By default the minimum length is 8, the maximum is 60.
 * At least two numbers or punctuations are needed.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Password implements Filter {
  /**
   * Minimum password length
   *
   * @var int
   */
  private $_minimumLength;

  /**
   * Maximum password length
   *
   * @var int
   */
  private $_maximumLength;

  /**
   * Construct object and initialize password length limits
   *
   * @param int $minimum
   * @param int $maximum
   */
  public function __construct($minimum = 8, $maximum = 60) {
    $this->_minimumLength = (int)$minimum;
    $this->_maximumLength = (int)$maximum;
  }

  /**
   * Check the password input and throw an exception if it does not match the condition.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    $length = \strlen($value);
    if ($length < $this->_minimumLength) {
      throw new Exception\InvalidLength\ToShort($this->_minimumLength, $length);
    }
    if ($length > $this->_maximumLength) {
      throw new Exception\InvalidLength\ToLong($this->_maximumLength, $length);
    }
    \preg_match_all('(\PL)u', $value, $matches);
    if (!(isset($matches[0]) && \count($matches[0]) > 1)) {
      throw new Exception\Password\Weak();
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
    try {
      $this->validate($value);
      return (string)$value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
