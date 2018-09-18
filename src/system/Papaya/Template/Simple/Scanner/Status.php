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

namespace Papaya\Template\Simple\Scanner;

/**
 * Abstract superclass for status objects used by the simple template scanner
 *
 * @package Papaya-Library
 * @subpackage Template
 */
abstract class Status {
  /**
   * Try to get token in buffer at offset position.
   *
   * @param string $buffer
   * @param int $offset
   * @return \Papaya\Template\Simple\Scanner\Token
   */
  abstract public function getToken($buffer, $offset);

  /**
   * Check if token ends status
   *
   * @param \Papaya\Template\Simple\Scanner\Token $token
   * @return bool
   */
  public function isEndToken(
    /* @noinspection PhpUnusedParameterInspection */
    $token
  ) {
    return FALSE;
  }

  /**
   * Get new (sub)status if needed.
   *
   * @param \Papaya\Template\Simple\Scanner\Token $token
   * @return self|null
   */
  public function getNewStatus(
    /* @noinspection PhpUnusedParameterInspection */
    $token
  ) {
    return;
  }

  /**
   * Checks if the given offset position matches the pattern.
   *
   * @param string $buffer
   * @param int $offset
   * @param string $pattern
   * @return string|null
   */
  protected function matchPattern($buffer, $offset, $pattern) {
    $found = \preg_match(
      $pattern, $buffer, $match, PREG_OFFSET_CAPTURE, $offset
    );
    if ($found &&
      isset($match[0]) &&
      isset($match[0][1]) &&
      $match[0][1] === $offset) {
      return $match[0][0];
    }
    return;
  }

  /**
   * Checks if the given offset position matches any pattern in the given list. The
   * list is an array with the patterns as keys and token types as values.
   *
   * @param string $buffer
   * @param int $offset
   * @param array $patterns
   * @internal param array $pattern
   * @return \Papaya\Template\Simple\Scanner\Token|null
   */
  protected function matchPatterns($buffer, $offset, $patterns) {
    foreach ($patterns as $pattern => $tokenType) {
      $tokenContent = $this->matchPattern($buffer, $offset, $pattern);
      if (NULL !== $tokenContent) {
        return new \Papaya\Template\Simple\Scanner\Token($tokenType, $offset, $tokenContent);
      }
    }
    return;
  }
}
