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
 * Abstract filter class implementing logical "OR" links between other filters
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class LogicalOr extends Logical {

  /**
   * Call validate() on subfilters, capture exceptions,
   * if a filter does not throw an exception break the loop and return TRUE.
   *
   * The method throws the first captured exception if all filter failed.
   *
   * @param string $value
   * @throws \Exception
   * @throws null
   * @throws \PapayaFilterException
   * @return bool
   */
  public function validate($value) {
    $firstException = NULL;
    /** @var \Papaya\Filter $filter */
    foreach ($this->_filters as $filter) {
      try {
        $filter->validate($value);
        return TRUE;
      } catch (\PapayaFilterException $e) {
        if (is_null($firstException)) {
          $firstException = $e;
        }
      }
    }
    throw $firstException;
  }

  /**
   * Call filter() on each subfilter while NULL is returned.
   *
   * @param string $value
   * @return mixed|null
   */
  public function filter($value) {
    /** @var \Papaya\Filter $filter */
    foreach ($this->_filters as $filter) {
      $filterValue = $filter->filter($value);
      if (!is_null($filterValue)) {
        return $filterValue;
      }
    }
    return NULL;
  }
}
