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

/**
* This class validates and filters IP addresses in version 6 form.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterIpV6 implements \PapayaFilter {

  /**
   * This method validates that an input string is a valid IP.
   *
   * 1. Split the value into its individual parts.
   * 2. Check each part: it must not contain invalid characters,
   *    and there must not be more than one empty part.
   *
   * @param string $value
   * @throws \PapayaFilterExceptionCountMismatch
   * @throws \PapayaFilterExceptionEmpty
   * @throws \PapayaFilterExceptionPartInvalid
   * @return boolean TRUE
   */
  public function validate($value) {
    if (empty($value)) {
      throw new \PapayaFilterExceptionEmpty();
    }
    $parts = explode(':', $value);
    $countEmpty = 0;
    $emptyPositions = array();
    foreach ($parts as $position => $part) {
      if (empty($part)) {
        $countEmpty++;
        $emptyPositions[] = $position;
      } elseif (!preg_match('(^[\da-f]{1,4}$)i', $part)) {
        throw new \PapayaFilterExceptionPartInvalid($position + 1, 'IPv6 part');
      }
    }
    if ($countEmpty > 2) {
      throw new \PapayaFilterExceptionCountMismatch(1, $countEmpty, 'empty IPv6 parts');
    } elseif ($countEmpty == 2) {
      $e1 = $emptyPositions[0];
      $e2 = $emptyPositions[1];
      if (!(($e1 == 0 && $e2 == 1) || ($e1 == count($parts) - 2 && $e2 == count($parts) - 1))) {
        throw new \PapayaFilterExceptionPartInvalid($e2 + 1, 'IPv6 parts');
      }
    } elseif ($countEmpty == 1 && count($parts) > 7) {
      throw new \PapayaFilterExceptionCountMismatch(7, count($parts), 'IPv6 parts');
    } elseif ($countEmpty == 0 && count($parts) != 8) {
      throw new \PapayaFilterExceptionCountMismatch(8, count($parts), 'IPv6 parts');
    }
    return TRUE;
  }

  /**
  * This method filters leading and trailing whitespaces from the input IP.
  *
  * @param string $value
  * @return mixed string|NULL
  */
  public function filter($value) {
    $result = trim($value);
    try {
      $this->validate($result);
    } catch(\PapayaFilterException $e) {
      $result = NULL;
    }
    return $result;
  }
}
