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
namespace Papaya\Filter\Text;

use Papaya\Filter;

/**
 * Papaya filter class for an string consisting of several parts
 *
 * It will explode the string by the provided separator and filter each part using
 * the provided element filter.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Explode implements Filter {
  const TRIM_TOKENS = 1;

  /**
   * @var string
   */
  private $_separator = ',';

  /**
   * @var Filter
   */
  private $_filter;

  /**
   * @var int
   */
  private $_options;

  public function __construct(
    $separator = NULL, Filter $elementFilter = NULL, $options = self::TRIM_TOKENS
  ) {
    if (\is_string($separator)) {
      $this->_separator = $separator;
    }
    $this->_filter = $elementFilter;
    $this->_options = $options;
  }

  /**
   * @param mixed $value
   *
   * @return bool
   *
   * @throws Filter\Exception
   */
  public function validate($value) {
    if (empty($value)) {
      throw new Filter\Exception\IsEmpty();
    }
    $tokens = \explode($this->_separator, (string)$value);
    if ($this->_filter instanceof Filter) {
      foreach ($tokens as $token) {
        if (\Papaya\Utility\Bitwise::inBitmask(self::TRIM_TOKENS, $this->_options)) {
          $token = \trim($token);
        }
        $this->_filter->validate($token);
      }
    }
    return TRUE;
  }

  /**
   * @param mixed $value
   *
   * @return array|null
   */
  public function filter($value) {
    $tokens = \explode($this->_separator, (string)$value);
    $result = [];
    foreach ($tokens as $token) {
      if (\Papaya\Utility\Bitwise::inBitmask(self::TRIM_TOKENS, $this->_options)) {
        $token = \trim($token);
      }
      if ($this->_filter instanceof Filter) {
        $filteredToken = $this->_filter->filter($token);
      } else {
        $filteredToken = empty($token) ? NULL : $token;
      }
      if (NULL !== $filteredToken) {
        $result[] = $filteredToken;
      }
    }
    return empty($result) ? NULL : $result;
  }
}
