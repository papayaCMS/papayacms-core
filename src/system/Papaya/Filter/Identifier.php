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

namespace Papaya\Filter {
  /**
   * Papaya filter class for an identifier/name
   *
   * Identifiers are only allowed to include ASCII letters, digits and underscore.
   * The upper/lowercase restriction is optional
   *
   * @package Papaya-Library
   * @subpackage Filter
   */
  class Identifier implements \Papaya\Filter {

    const CASE_INSENSITIVE = 0;
    const LOWERCASE = 1;
    const UPPERCASE = 2;

    private $_minimumLength;
    private $_maximumLength;
    private $_mode;

    /**
     * @param int $minimumLength
     * @param int $maximumLength
     * @param int $mode
     */
    public function __construct($minimumLength = 1, $maximumLength = 0, $mode = self::CASE_INSENSITIVE) {
      $this->_minimumLength = (int)$minimumLength;
      $this->_maximumLength = (int)$maximumLength;
      if ($this->_minimumLength < 0) {
        throw new \InvalidArgumentException(
          'Minimum length must be greater then 0.'
        );
      }
      if ($this->_maximumLength < 0) {
        throw new \InvalidArgumentException(
          'Maximum length must be greater then 0.'
        );
      }
      if ($this->_minimumLength > $this->_maximumLength && $this->_maximumLength > 0) {
        throw new \InvalidArgumentException(
          'Maximum length must be greater or equal to minimum length. Use 0 for no maximum length.'
        );
      }
      $this->_mode = (int)$mode;
    }

    /**
     * @param mixed|NULL $value
     * @return bool|mixed|null|string|string[]
     */
    public function filter($value) {
      $value = preg_replace('([^a-zA-Z\d_]+)', '', (string)$value);
      if ($this->_maximumLength > 0) {
        $value = substr($value, 0, $this->_maximumLength);
      }
      if (strlen($value) < $this->_minimumLength) {
        return NULL;
      }
      if ($this->_mode === self::LOWERCASE) {
        return strtolower($value);
      }
      if ($this->_mode === self::UPPERCASE) {
        return strtoupper($value);
      }
      return $value;
    }

    /**
     * @param mixed $value
     * @return bool
     * @throws Exception\InvalidValue
     * @throws Exception\UnexpectedType
     */
    public function validate($value) {
      if (!is_string($value)) {
        throw new Exception\UnexpectedType('string');
      }
      switch ($this->_mode) {
      case self::LOWERCASE :
        $pattern = '(^[a-z\d_]{%d,%s}+$)D';
        break;
      case self::UPPERCASE :
        $pattern = '(^[A-Z\d_]{%d,%s}+$)D';
        break;
      default:
        $pattern = '(^[a-zA-Z\d_]{%d,%s}+$)D';
        break;
      }
      $pattern = sprintf($pattern, $this->_minimumLength, $this->_maximumLength ?: '');
      if (!preg_match($pattern, $value)) {
        throw new Exception\InvalidValue($value);
      }
      return TRUE;
    }
  }
}
