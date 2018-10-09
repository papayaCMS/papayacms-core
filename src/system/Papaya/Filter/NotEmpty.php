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
 * Validate that a value contains at least one character
 *
 * By default whitespace chars are ignored, too.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NotEmpty implements Filter {
  /**
   * Values with only whitespaces are considered empty, too.
   *
   * @var bool
   */
  private $_ignoreSpaces;

  /**
   * Initialize object and store ignore option.
   *
   * @param bool $ignoreSpaces
   */
  public function __construct($ignoreSpaces = TRUE) {
    \Papaya\Utility\Constraints::assertBoolean($ignoreSpaces);
    $this->_ignoreSpaces = $ignoreSpaces;
  }

  /**
   * Check for empty string. If $value is not empty and whitespace are ignored,
   * check the trimmed version, too.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return bool
   */
  public function validate($value) {
    if (NULL !== $value && \is_array($value)) {
      if (\count($value) <= 0) {
        throw new Exception\IsEmpty();
      }
    } else {
      $value = (string)$value;
      if ('' === $value ||
        ($this->_ignoreSpaces && '' === \trim($value))) {
        throw new Exception\IsEmpty();
      }
    }
    return TRUE;
  }

  /**
   * If spaces are ignored trim the value. If the value is empty return NULL.
   *
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    if (NULL !== $value && \is_array($value)) {
      return (\count($value) > 0) ? $value : NULL;
    }
    if ($this->_ignoreSpaces) {
      $value = \trim($value);
    }
    return ('' === $value) ? NULL : (string)$value;
  }
}
