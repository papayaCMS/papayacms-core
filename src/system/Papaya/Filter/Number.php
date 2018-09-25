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
 * Papaya filter class for numbers with a specific length, e.g. credit card or account numbers
 *
 * Unsigned integer numbers without sign that can also start with one or more zeros.
 * Optionally, a minimum and/or maximum number of digits can be set.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Number implements Filter {
  /**
   * Minimum number of digits
   *
   * @var int
   */
  private $_minimumLength;

  /**
   * Maximum number of digits
   *
   * @var int
   */
  private $_maximumLength;

  /**
   * Constructor
   *
   * @param int $minimumLength optional, default NULL
   * @param int $maximumLength optional, default NULL
   *
   * @throws \UnexpectedValueException
   */
  public function __construct($minimumLength = NULL, $maximumLength = NULL) {
    if (NULL !== $minimumLength) {
      if (!\is_numeric($minimumLength) || $minimumLength <= 0) {
        throw new \UnexpectedValueException('Minimum length must be greater than 0.');
      }
    }
    if (NULL !== $maximumLength) {
      if (!\is_numeric($maximumLength) || $maximumLength <= 0) {
        throw new \UnexpectedValueException('Maximum length must be greater than 0.');
      }
      if (NULL !== $minimumLength && $minimumLength > $maximumLength) {
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
   *
   * @param mixed $value
   *
   * @throws Exception\UnexpectedType
   * @throws Exception\OutOfRange\ToSmall
   * @throws Exception\OutOfRange\ToLarge
   *
   * @return bool
   */
  public function validate($value) {
    $value = (string)$value;
    if (!\preg_match('(^\d+$)', $value)) {
      throw new Exception\UnexpectedType('number');
    }
    if (NULL !== $this->_minimumLength && \strlen($value) < $this->_minimumLength) {
      throw new Exception\OutOfRange\ToSmall($this->_minimumLength, \strlen($value));
    }
    if (NULL !== $this->_maximumLength && \strlen($value) > $this->_maximumLength) {
      throw new Exception\OutOfRange\ToLarge($this->_maximumLength, \strlen($value));
    }
    return TRUE;
  }

  /**
   * Filter a value
   *
   * @param mixed $value
   *
   * @return mixed the filtered value or NULL if not valid
   */
  public function filter($value) {
    try {
      $this->validate(\trim($value));
    } catch (Exception $e) {
      return NULL;
    }
    return \trim($value);
  }
}
