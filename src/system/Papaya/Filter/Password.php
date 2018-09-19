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

/**
 * Papaya filter class for a password
 *
 * This class is used to validate/filter a password input.
 * By default the minimum length is 8, the maximum is 60.
 * At least two numbers or punktuations are needed.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Password implements \Papaya\Filter {
  /**
   * Minimum password length
   *
   * @var int
   */
  private $_minimumLength = 0;

  /**
   * Maximum password length
   *
   * @var int
   */
  private $_maximumLength = 0;

  /**
   * Construct object and initilize password length limits
   *
   * @param int $minimum
   * @param int $maximum
   */
  public function __construct($minimum = 8, $maximum = 60) {
    $this->_minimumLength = $minimum;
    $this->_maximumLength = $maximum;
  }

  /**
   * Check the password input and throw an exception if it does not match the condition.
   *
   * @throws \Papaya\Filter\Exception
   *
   * @param string $value
   *
   * @return true
   */
  public function validate($value) {
    $length = \strlen($value);
    if ($length < $this->_minimumLength) {
      throw new \Papaya\Filter\Exception\InvalidLength\ToShort($this->_minimumLength, $length);
    }
    if ($length > $this->_maximumLength) {
      throw new \Papaya\Filter\Exception\InvalidLength\ToLong($this->_maximumLength, $length);
    }
    \preg_match_all('(\PL)u', $value, $matches);
    if (!(isset($matches[0]) && \count($matches[0]) > 1)) {
      throw new \Papaya\Filter\Exception\Password\Weak();
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param string $value
   *
   * @return string|null
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (\Papaya\Filter\Exception $e) {
      return;
    }
  }
}
