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
namespace Papaya\Filter\Ip;

use Papaya\Filter;

/**
 * This class validates and filters IP addresses in version 6 form.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class V6 implements Filter {
  /**
   * This method validates that an input string is a valid IP.
   *
   * 1. Split the value into its individual parts.
   * 2. Check each part: it must not contain invalid characters,
   *    and there must not be more than one empty part.
   *
   * @param mixed $value
   *
   * @throws Filter\Exception\InvalidCount
   * @throws Filter\Exception\IsEmpty
   * @throws Filter\Exception\InvalidPart
   *
   * @return bool TRUE
   */
  public function validate($value) {
    if (empty($value)) {
      throw new Filter\Exception\IsEmpty();
    }
    $parts = \explode(':', $value);
    $countEmpty = 0;
    $emptyPositions = [];
    foreach ($parts as $position => $part) {
      if (empty($part)) {
        $countEmpty++;
        $emptyPositions[] = $position;
      } elseif (!\preg_match('(^[\da-f]{1,4}$)i', $part)) {
        throw new Filter\Exception\InvalidPart($position + 1, 'IPv6 part');
      }
    }
    if ($countEmpty > 2) {
      throw new Filter\Exception\InvalidCount(1, $countEmpty, 'empty IPv6 parts');
    }
    if (2 === $countEmpty) {
      list($e1, $e2) = $emptyPositions;
      if (!((0 === $e1 && 1 === $e2) || ($e1 === \count($parts) - 2 && $e2 === \count($parts) - 1))) {
        throw new Filter\Exception\InvalidPart($e2 + 1, 'IPv6 parts');
      }
    } elseif (1 === $countEmpty && \count($parts) > 7) {
      throw new Filter\Exception\InvalidCount(7, \count($parts), 'IPv6 parts');
    } elseif (0 === $countEmpty && 8 !== \count($parts)) {
      throw new Filter\Exception\InvalidCount(8, \count($parts), 'IPv6 parts');
    }
    return TRUE;
  }

  /**
   * This method filters leading and trailing whitespaces from the input IP.
   *
   * @param mixed $value
   *
   * @return string|null
   */
  public function filter($value) {
    $result = \trim($value);
    try {
      $this->validate($result);
    } catch (Filter\Exception $e) {
      $result = NULL;
    }
    return $result;
  }
}
